

<!-- Search Form - used in main page
Where user enters gene,drug, relation type to search database
You have the option to generate an LLM Report as well 
-->

<form id="gene-drug-search-form" class="card" method="POST">
    <div>
        <label for="gene">Gene</label>
        <input type="text" id="gene" name="gene" placeholder="e.g., CYP2D6" />
    </div>
    <div>
        <label for="drug">Drug</label>
        <input type="text" id="drug" name="drug" placeholder="e.g., Fluoxetine" />
    </div>

    <div>
        <label for="relation_type">Relation type</label>
        <select id="relation_type" name="relation_type">
            <option value="">— Any —</option>
            <option value="inhibitor">Inhibitor</option>
            <option value="upregulator">Upregulator</option>
            <option value="snp_specific">SNP-specific response</option>
            <option value="vaccine">Vaccine</option>
            <option value="negative modulator">Negative modulator</option>
        </select>
    </div>

    <div>
        <label for="want_llm">LLM Report</label>
        <select id="want_llm" name="want_llm">
            <option value="no">No</option>
            <option value="yes">Yes</option>
        </select>
    </div>

    <div class="full btns">
        <button type="submit" name="action" value="query">Search</button>
    </div>
</form>