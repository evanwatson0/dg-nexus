<?php

namespace App\Services;

use mysqli;

class GeneDrugDataPipeline
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    // /**
    //  * Retrieves all tuples matching the gene, drug and relation of interest.
    //  * If the any of these params are empty, they will have a wildcard match instead
    //  * Prerequisites: Gene and Drug both can't be NULL!
    //  * 
    //  * Outputs JSON object, the return["data"] contains the following fields
    //  * - GeneSymbol
    //  * - GeneLongName
    //  * - DrugName
    //  * - RelationType
    //  * - Notes
    //  * - Citations
    //  * 
    //  * 
    //  * @param mixed $gene
    //  * @param mixed $drug
    //  * @param mixed $relation_type
    //  * @return void
    //  */
    public function retrieveRelations(
        ?string $gene,
        ?string $drug,
        ?string $relationType
    ): array {
        // Normalize inputs
        $gene = trim($gene ?? '');
        $drug = trim($drug ?? '');
        $relationType = trim($relationType ?? '');

        // If everything empty, return empty result
        if ($gene === '' && $drug === '') {
            return [];
        }

        // Use REGEXP wildcards when empty
        $genePattern = $gene === '' ? '.*' : $gene;
        $drugPattern = $drug === '' ? '.*' : $drug;
        $relationPattern = $relationType === '' ? '.*' : $relationType;

        $sql = "
            SELECT DISTINCT
                g.GeneSymbol,
                g.GeneLongName,
                d.DrugName,
                i.RelationType,
                i.Notes,
                GROUP_CONCAT(
                    CONCAT(c.Source, ' (PMID:', c.PMID, ')')
                    SEPARATOR '; '
                ) AS Citations
            FROM Interaction i
            LEFT JOIN Gene g ON g.GeneID = i.GeneID
            LEFT JOIN Drug d ON d.DrugID = i.DrugID
            LEFT JOIN Interaction_Citation ic ON ic.InteractionID = i.InteractionID
            LEFT JOIN Citation c ON c.CitationID = ic.CitationID
            WHERE (g.GeneSymbol REGEXP ? OR g.GeneLongName REGEXP ?)
            AND d.DrugName REGEXP ?
            AND i.RelationType REGEXP ?
            AND NOT d.DrugName REGEXP 'Chembl'
            GROUP BY 
                i.InteractionID,
                g.GeneSymbol,
                g.GeneLongName,
                d.DrugName,
                i.RelationType,
                i.Notes
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            'ssss',
            $genePattern,
            $genePattern,
            $drugPattern,
            $relationPattern
        );
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }


    /**
     * Main entry point: inserts all DGI-db interactions
     */
    public function insertRelations(array $queries): void
    {
        foreach ($queries['nodes'][0]['interactions'] as $query) {

            if (empty($query['gene']['name'])) {
                continue;
            }

            $geneId = $this->insertGene(
                $query['gene']['name'],
                $query['gene']['longName'] ?? null,
                $query['gene']['conceptId'] ?? null
            );

            $drugId = $this->insertDrug(
                $query['drug']['name'] ?? null
            );

            $interactionId = $this->insertInteraction(
                $drugId,
                $geneId,
                $query['interactionTypes'][0]['type'] ?? 'Unknown/Not Applicable',
                $query['evidenceScore'] ?? null,
                $query['interactionScore'] ?? null,
                $query['drugSpecificity'] ?? null
            );

            foreach ($query['publications'] as $publication) {
                $citationId = $this->insertCitation(
                    $publication['pmid'] ?? null,
                    $publication['citation'] ?? null
                );

                $this->linkInteractionCitation($interactionId, $citationId);
            }
        }
    }

    /* ---------------------------------------------------------
       PRIVATE HELPERS
    --------------------------------------------------------- */

    private function insertDrug(?string $drugName): int
    {
        $stmt = $this->conn->prepare(
            'SELECT DrugID FROM Drug WHERE DrugName = ?'
        );
        $stmt->bind_param('s', $drugName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return (int)$row['DrugID'];
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO Drug (DrugName) VALUES (?)'
        );
        $stmt->bind_param('s', $drugName);
        $stmt->execute();

        return $stmt->insert_id;
    }

    private function insertGene(string $name, ?string $longName, ?string $hgnc): int
    {
        $stmt = $this->conn->prepare(
            'SELECT GeneID FROM Gene 
             WHERE GeneSymbol = ? OR GeneLongName = ?'
        );
        $stmt->bind_param('ss', $name, $longName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return (int)$row['GeneID'];
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO Gene (GeneSymbol, GeneLongName, HGNC)
             VALUES (?, ?, ?)'
        );
        $stmt->bind_param('sss', $name, $longName, $hgnc);
        $stmt->execute();

        return $stmt->insert_id;
    }

    private function insertInteraction(
        int $drugId,
        int $geneId,
        string $relationType,
        $evidenceScore,
        $interactionScore,
        $drugSpecificity
    ): int {
        $stmt = $this->conn->prepare(
            'SELECT InteractionID FROM Interaction
             WHERE DrugID = ? AND GeneID = ?'
        );
        $stmt->bind_param('ii', $drugId, $geneId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return (int)$row['InteractionID'];
        }

        $notes = "Interaction Score=$interactionScore; Evidence Score=$evidenceScore; Drug Specificity=$drugSpecificity";

        $stmt = $this->conn->prepare(
            'INSERT INTO Interaction (DrugID, GeneID, RelationType, Notes)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('iiss', $drugId, $geneId, $relationType, $notes);
        $stmt->execute();

        return $stmt->insert_id;
    }

    private function insertCitation(?string $pmid, ?string $citation): int
    {
        $stmt = $this->conn->prepare(
            'SELECT CitationID FROM Citation WHERE Source = ? AND PMID = ?'
        );
        $stmt->bind_param('ss', $citation, $pmid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return (int)$row['CitationID'];
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO Citation (PMID, Source) VALUES (?, ?)'
        );
        $stmt->bind_param('ss', $pmid, $citation);
        $stmt->execute();

        return $stmt->insert_id;
    }

    private function linkInteractionCitation(int $interactionId, int $citationId): void
    {
        $stmt = $this->conn->prepare(
            'SELECT 1 FROM Interaction_Citation
             WHERE InteractionID = ? AND CitationID = ?'
        );
        $stmt->bind_param('ii', $interactionId, $citationId);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            return;
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO Interaction_Citation (InteractionID, CitationID)
             VALUES (?, ?)'
        );
        $stmt->bind_param('ii', $interactionId, $citationId);
        $stmt->execute();
    }
}
