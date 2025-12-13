# ❤️ **README — Database Setup (Prepared by Lell)**

## 📌 Overview

This folder contains the **complete database dump** for our project:
**Drug–Gene Interaction Explorer (BIOM9450)**.

The dump file will recreate the entire database structure and all data, including:

* Normalised biomedical tables (drug, gene, interaction)
* LLM workflow tables (llm_session, llm_prompt, llm_response)
* All indexes and foreign key relationships
* Full dataset imported from **DGIdb (Drug–Gene Interaction Database)**
  for the five core genes we selected

You can import the dump and immediately run the backend without any manual data entry.

---

## 📦 Files Included

### **`druggene_db_full_dump.sql`**

This is the **self-contained SQL file** containing:

* CREATE DATABASE statements
* CREATE TABLE statements
* All INSERT statements (≈ 7,668 interaction records)
* Table indexes and foreign keys
* All drug and gene entries
* All LLM-related tables (empty by default but ready to use)

This file fully reconstructs the database on any machine.

---

## 🚀 How to Import the Database

### **Using MySQL Workbench**

1. Open MySQL Workbench
2. Go to:
   **Server → Data Import**
3. Select:
   **Import from Self-Contained File**
4. Choose:
   `druggene_db_full_dump.sql`
5. Create a new schema (e.g., `druggene_db`)
6. Select that schema as the “Default Target Schema”
7. Click **Start Import**

After the import, you will have a fully working database identical to Lell’s version.

---

## 🧬 Genes Included in This Dataset

We imported high-quality curated interaction data for **five core pharmacogenomic / cancer-related genes**:

| Gene Symbol | Description (Short)               |
| ----------- | --------------------------------- |
| **EGFR**    | Epidermal Growth Factor Receptor  |
| **TP53**    | Tumor Protein p53                 |
| **CYP2D6**  | Major drug-metabolizing enzyme    |
| **BRCA1**   | Breast cancer susceptibility gene |
| **KRAS**    | Common oncogenic driver           |

These genes were chosen because they:

* Have rich drug–gene interaction profiles
* Are clinically relevant
* Provide enough interaction data for testing the application
* Allow us to evaluate both backend queries and LLM summaries

---

## 💊 Drug & Interaction Data

### The database includes:

* **Hundreds of unique drugs**, obtained through DGIdb relations with the five genes
* **7,668 curated interaction records**, each containing:

  * DrugID (foreign key)
  * GeneID (foreign key)
  * RelationType
    (e.g., *inhibitor*, *activator*, *agonist*, *antagonist*, *substrate*, etc.)
  * Notes (source database of the interaction)

### Example Use Cases for the Team

* Claire can query drug → gene or gene → drug for frontend display
* Evan can feed interaction results into prompt templates for LLM summarisation
* Anyone can visualise the network graph
* The database can be queried via simple endpoints:

  * `/api/gene?symbol=CYP2D6`
  * `/api/drug?name=Gefitinib`
  * `/api/interactions?gene=EGFR`

The backend will return real biomedical data ready for the UI and LLM.

---

## 🧠 LLM-Related Tables

These tables are empty initially (by design) and will be populated at runtime:

* **llm_session** – user session tracking
* **llm_prompt** – full text of generated prompts
* **llm_response** – model responses + ratings + feedback support

No manual data import is required for these tables.
They are populated automatically when the system runs.

---

## 📚 Citation Tables (Optional)

Two tables are included for future integration:

* **citation**
* **interaction_citation**

The current DGIdb dataset does not include PMID information in this release,
so these tables are intentionally left empty but fully functional if we later integrate PubMed.

---

## ✔️ Summary

This database provides:

* Clean, normalised tables
* High-quality DGIdb interaction data
* Ready-to-use drug and gene entities
* Fully populated interaction network
* LLM integration tables
* A complete SQL dump enabling one-click setup

**All team members can now run the backend using the same consistent dataset.**


