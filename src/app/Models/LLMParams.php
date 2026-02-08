<?php
/**
 * llm_params.php: LLM Prompt Engineering	Technique
 * Author: Evan
 * 
 * Imported by llm_request.php
 * Contains the prompt engineering parameters for the LLM
 */


namespace App\Models;

class LLMParams {

  private static $model = 'gpt-4.1-nano-2025-04-14';
  private static $temperature = 0.6;
  
  /* --------------------------------------------------------------
    Prompt Text
  -------------------------------------------------------------- */
  private static string $report_system=<<<'PROMPT'
    You are an expert clinicalian with a reliable knowledge in genetic relations
    between drugs. You only give accurate medical explanations according to the literature
    and you cite real diagnostic criteria and biological mechanisms when relevant. 
    You only write in PARAGRAPHS.

    You will be given an input of data containing drug/gene relationships and the type of 
    relationship they have. Other metadata concerning the relation will also be provided. Select
    Particular Drugs and Genes of Interest which have high Interaction Scores, Evidence Scores, Specificity Scores 
    and ideally have a reference to literatures  

    If one gene is found in these interactions, treat it as a report concerning this gene. 
    If one DRUG is found in these interactions, the report is focused on that drug & it's interactions 



    When providing you're references, please ensure you provide the direct https LINKS to access the data
    Do Not write any tables in! Only Write an essential information section, then produce the report paragraphs,
    under the subheading 'Interaction Analysis'. You do not produce any other subheadings other than Overview, Interaction
    Analysis, References and Further Readings
    
    ---

    Structure of the Report: 
    
    <h2> Overview </h2>
    briefly describe the function; for gene include it's involved pathways, interactions with other genes
    and common diseases it's associated with (NO LYING). For drug, it's class, the pathways it affects, 
    and common disease literature associates it with.
    

    <h2> Interaction Analysis </h2>
    Select a subset of most relevant drug/genes (3-5 drugs/gene) or gene pathways / drug CLASSES to analyse.
    Selection is based on NOTES column if available, which contains interaction score, evidence score and drug specificity.
    When discussing the interaction scores in the report, YOU MUST INCLUDE a sentence which describes how users can learn what
    these scores mean using this link https://dgidb.org/about/overview/interaction-score
    
    For each drug/gene describe how the interaction works, using data from the sources. Produce 2-3 paragraphs in this section

    <h3> References and Further Readings </h3>

    Include the references to each of the Sources tied to these interactions here, keep it in the same consistent format that it was stored in.
    
    --- 

    Requirements: 
    NEVER include italics, bold, tables.
    Output the heads with <h2> htmls, and the paragraphs with <p>. Don't include any other html    
    Output should be for the report only
  PROMPT;

  private static string $report_assistant = <<<'PROMPT'
    <h2>Overview</h2> 
    <p>EGFR (Epidermal Growth Factor Receptor) encodes a transmembrane receptor tyrosine kinase located on chromosome 7p11.2. 
    It is involved in regulating cell proliferation, survival, differentiation, and migration. Upon binding its ligands, such as EGF or TGF-α, 
    EGFR activates multiple downstream pathways including the MAPK/ERK pathway, PI3K/AKT pathway, and JAK/STAT pathway. Dysregulation of EGFR through mutations, 
    amplification, or overexpression is associated with diseases such as non-small cell lung cancer (NSCLC), colorectal cancer, and glioblastoma.</p> 
    
    <h2>Interaction Analysis</h2> 
    <p>Selected interactions are based on high interaction scores, evidence scores, and specificity scores. 
    You can learn more about these scores here: <a href="https://dgidb.org/about/overview/interaction-score">DGIdb Interaction Scores</a>.</p> 
    
    <p>Gefitinib (First-Generation Tyrosine Kinase Inhibitor): Gefitinib binds reversibly to the intracellular ATP-binding domain of EGFR, inhibiting 
    downstream signalling through the MAPK and PI3K pathways. It is particularly effective against EGFR mutations L858R and exon 19 deletions. 
    Limitations include development of resistance, most notably via the T790M mutation.</p> 
    
    <p>Afatinib (Second-Generation Tyrosine Kinase Inhibitor): Afatinib binds irreversibly to EGFR, HER2, and HER4, providing 
    broader inhibition of EGFR-family signalling. It is used in patients with resistant EGFR mutations but has higher toxicity compared 
    to first-generation inhibitors.</p> 
    
    <p>Osimertinib (Third-Generation Tyrosine Kinase Inhibitor): Osimertinib selectively inhibits mutant EGFR, 
    including T790M resistance mutations, offering improved tolerability and efficacy in NSCLC. 
    It is often used as first-line therapy for EGFR-mutant NSCLC.</p> 
    
    <p>Cetuximab (Monoclonal Antibody): Cetuximab targets the extracellular domain of EGFR, blocking ligand binding and receptor activation. 
    It is primarily used in colorectal cancer and head and neck cancers. 
    Effectiveness depends on wild-type KRAS and NRAS status.</p> 
    
    <p>Panitumumab (Monoclonal Antibody): Similar to cetuximab, panitumumab binds the extracellular EGFR domain. 
    It is a fully human antibody, reducing immunogenicity, and is used in KRAS/NRAS wild-type colorectal cancers.</p> 
    
    <h3>References and Further Readings</h3> 
    <p> DGIdb: EGFR interactions and scores — <a href="https://www.dgidb.org/gene/EGFR">https://www.dgidb.org/gene/EGFR</a></p> 
    <p> National Cancer Institute, EGFR Targeted Therapies — <a href="https://www.cancer.gov/about-cancer/treatment/drugs/egfr-inhibitors">https://www.cancer.gov/about-cancer/treatment/drugs/egfr-inhibitors</a></p> 
    <p> Cross, D. A. et al., “AZD9291, an irreversible EGFR TKI, overcomes T790M-mediated resistance,” Cancer Discovery 2014 — <a href="https://cancerdiscovery.aacrjournals.org/content/4/9/1046">https://cancerdiscovery.aacrjournals.org/content/4/9/1046</a></p> 
    <p>Li, S. et al., “EGFR-targeted monoclonal antibodies in cancer therapy,” Clinical Cancer Research 2018 — <a href="https://clincancerres.aacrjournals.org/content/24/15/3347">https://clincancerres.aacrjournals.org/content/24/15/3347</a></p>
  PROMPT;

  /**
   * @var array
   * Prompt Engineering strategy for llm. Upon user request to ask LLM about the given data,
   * The LLM is conditioned using these queries to minimise hallucination
   * Upon user request to ask LLM 
   * 
   * "user", "assistant", "system"
   */
  private static string $user_chat_system_text = <<<'PROMPT'
  As a biomedical expert, you will then respond to users asking queries pertaining to the report you produced, and the 
  previously described drug gene relationships. Respond to the users questions with your knowledge, producing a paragraph response.
  You only give a pure text output, do NOT include any html elements. You only cite links you were given in previous inputs.
  You only respond to queries within a BIOMEDICAL context. You link back to your report when you can
  PROMPT;

  private static string $user_chat_user_text = <<<PROMPT
    Tell me more about Gefitinib, and it's current use in the medical domain.
  PROMPT;

  private static string $user_chat_assistant_text = <<<PROMPT
    Gefitinib is a first-generation tyrosine kinase inhibitor (TKI) that selectively targets the intracellular ATP-binding
    domain of the Epidermal Growth Factor Receptor (EGFR). By blocking EGFR signalling, it inhibits downstream
    pathways such as MAPK/ERK and PI3K/AKT, which are crucial for cell proliferation and survival. 
    Gefitinib is primarily used in the treatment of non-small cell lung cancer (NSCLC) in patients 
    whose tumors harbor activating EGFR mutations, specifically the L858R point mutation and exon 19 deletions. 
    It is generally prescribed as a first-line therapy in these cases. While initially effective, resistance often develops, 
    most notably via the T790M mutation, which can limit long-term efficacy. Ongoing clinical strategies include sequencing 
    Gefitinib with second- or third-generation TKIs, such as Osimertinib, to overcome resistance and maintain therapeutic benefit.
  PROMPT;

  /* --------------------------------------------------------------
    Prompt Builders
  -------------------------------------------------------------- */ 
  public static function make_user_query_prompt($user_query): string {
    $query_string = "
      As a biomedical and clinical expert, pretend you will be asked a user
      submitted query concerning data your report has generated. Return a concise,
      1 paragraph explanation to the user submitted query, looking online and providing
      relevant readings ONLY if it can be found. 
      explanation in response to a user submitted query. Ensure question is relevant
      to your position as an expert. Give only the answer to the user query in your response.

      The user query is: 
    " . $user_query;

    return $query_string;
  }

  /**
   * Parses Query Data into a string input used to send to ChatGPT
   * @param array $query_data
   * @return string
   */
  public static function make_report_prompt(array $query_data, string $input, string $type, string $relation_type): string {
    
    $prompt_string = "Generate a report for the " . $type . ", " . $input . "The queries are as follows: \n";

    $query_string = '';
    foreach ($query_data as $entry) {
      $gene = $entry['gene'] ?? '';
      $drug = $entry['drug'] ?? '';
      $relation_type = $entry['relation'] ?? '';
      $note = $entry['notes'] ?? '';
      $source = $entry['source'] ?? '';

      $query_string .= "Gene: $gene, Drug: $drug, Relation Type: $relation_type, Sources $source, Additional Notes: $note\n";
    }

    return $prompt_string . $query_string;
  }


  /* --------------------------------------------------------------
    Getters
  -------------------------------------------------------------- */ 
  public static function getModel(): string {
    return self::$model;
  }

  public static function getTemperature() {
    return self::$temperature;
  }

  public static function getOpenAIKey() {
    return getenv('OPENAI_API_KEY');
  }

  public static function getInitialisationPrompts() {
    return [
      [
        "role" => "system",
        "content" => [ ["type" => "text", "text" => self::$report_system] ]
      ],
      [
        "role" => "assistant",
        "content" => [ ["type" => "text", "text" => self::$report_assistant] ]
      ]
    ];
  }

  public static function getUserChatSystem() {
    return [
      "role" => "system",
      "content" => [ ["type" => "text", "text" => self::$user_chat_system_text] ]
    ];
  }

  public static function getUserChatUser() {
  return [
      "role" => "system",
      "content" => [ ["type" => "text", "text" => self::$user_chat_user_text] ]
    ];
  }
  
  public static function getUserChatAssistant() {
    return [
      "role" => "system",
      "content" => [ ["type" => "text", "text" => self::$user_chat_assistant_text] ]
    ];
  }
}




