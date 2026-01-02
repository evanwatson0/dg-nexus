
/**
 * Author: Claire
 * 
 * 把後端或 DGIdb 的回傳，轉成統一列格式
 * 期望欄位：drug, gene, relation, pmid, source
 * 
 */
function normaliseToRows(payload) {
    // 1) 若已是標準列陣列
    if (Array.isArray(payload) && payload.length && payload[0].drug && payload[0].gene) {
        return payload;
    }

    if (Array.isArray(payload) && payload.length && payload[0].GeneSymbol !== undefined) {
        return payload.map(row => ({
            gene: row.GeneLongName || row.GeneSymbol || "",
            drug: row.DrugName || "",
            relation: (row.RelationType || "").toLowerCase(),
            source: row.Citations  || "",
            notes: row.Notes
        }));
    }
}


/**
 * Author: Claire 
 * 
 * @param {*} rows 
 * @returns 
 */
function renderTable(rows) {
    if (rows === undefined) {
        tableEl.innerHTML = '<div class="muted">No data</div>';
        return;   
    }
    if (!rows.length) {
        tableEl.innerHTML = '<div class="muted">No data</div>';
        return;
    }
    const head = `
    <table>
        <thead>
        <tr>
            <th>Drug</th><th>Gene</th><th>Relation</th><th>Source</th><th>Notes</th>
        </tr>
        </thead>
        <tbody>
    `;
    const body = rows.map(r => `
    <tr>
        <td>${esc(r.drug)}</td>
        <td>${esc(r.gene)}</td>
        <td>${esc(r.relation || '')}</td>
        <td>${esc(r.source || '')}</td>
        <td>${esc(r.notes || '')}</td>
    </tr>
    `).join('');
    const foot = '</tbody></table>';
    tableEl.innerHTML = head + body + foot;
}


/**
 * Author: Claire 
 * 
 * 簡易 SVG 網路圖：把 gene 放左邊，drug 放右邊，中間拉線
 */
function renderMiniGraph(rows) {
    if (rows === undefined) {
        return;   
    }

    while (svg.firstChild) svg.removeChild(svg.firstChild);
    // 新增：記錄節點位置與所有線的引用
    const nodePositions = {}; // nodeName -> {x,y}
    const lineRefs = [];      // {lineEl, gene, drug}

    if (!rows.length) {
        const t = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        t.setAttribute('x', 20); t.setAttribute('y', 30);
        t.textContent = 'No data to visualize';
        svg.appendChild(t);
        return;
    }

    const genes = [...new Set(rows.map(r => r.gene).filter(Boolean))];
    const drugs = [...new Set(rows.map(r => r.drug).filter(Boolean))];

    const W = svg.clientWidth || 800;
    const H = svg.clientHeight || 360;
    const leftX = 120, rightX = W - 120;
    const padTop = 40, padBottom = 40;
    const gy = d3scalePositions(genes.length, H, padTop, padBottom);
    const dy = d3scalePositions(drugs.length, H, padTop, padBottom);

    // 線
    rows.forEach(r => {
        const gi = genes.indexOf(r.gene);
        const di = drugs.indexOf(r.drug);
        if (gi < 0 || di < 0) return;

        const line = document.createElementNS('http://www.w3.org/2000/svg','line');
        line.setAttribute('x1', leftX); line.setAttribute('y1', gy[gi]);
        line.setAttribute('x2', rightX); line.setAttribute('y2', dy[di]);
        line.setAttribute('stroke', '#cbd5e1'); line.setAttribute('stroke-width', '1.5');
        svg.appendChild(line);

        // ⭐⭐ 新增：把這條線存起來
        lineRefs.push({
            lineEl: line,
            gene: r.gene,
            drug: r.drug
        });

        // 在中點放標籤
        const mx = (leftX + rightX)/2, my = (gy[gi] + dy[di]) / 2;
        const label = document.createElementNS('http://www.w3.org/2000/svg','text');
        label.setAttribute('x', mx); label.setAttribute('y', my - 4);
        label.setAttribute('text-anchor', 'middle');
        label.setAttribute('font-size', '11');
        label.setAttribute('fill', '#64748b');
        label.textContent = (r.relation || '').toLowerCase();
        svg.appendChild(label);
        line._label = label; //Tie the label to the line
    });

    // 基因節點
    genes.forEach((g, i) => nodeCircle(leftX, gy[i], g, '#10b981', lineRefs, nodePositions));    
    // 藥物節點
    drugs.forEach((d, i) => nodeCircle(rightX, dy[i], d, '#3b82f6', lineRefs, nodePositions));

    function nodeCircle(x, y, name, color, lineRefs, nodePositions) {
        nodePositions[name] = { x, y };

        const circle = document.createElementNS('http://www.w3.org/2000/svg','circle');
        circle.setAttribute('cx', x);
        circle.setAttribute('cy', y);
        circle.setAttribute('r', 18);
        circle.setAttribute('fill', '#fff');
        circle.setAttribute('stroke', color);
        circle.setAttribute('stroke-width','2');
        circle.style.cursor = 'grab';
        svg.appendChild(circle);

        const text = document.createElementNS('http://www.w3.org/2000/svg','text');
        text.setAttribute('x', x);
        text.setAttribute('y', y + 4);
        text.setAttribute('text-anchor','middle');
        text.setAttribute('font-size','11');
        text.textContent = name;
        svg.appendChild(text);

        let dragging = false, ox = 0, oy = 0;

        circle.addEventListener('mousedown', e => {
            dragging = true;
            circle.style.cursor = 'grabbing';
            ox = e.clientX - x;
            oy = e.clientY - y;
        });

        document.addEventListener('mouseup', () => {
            dragging = false;
            circle.style.cursor = 'grab';
        });

        document.addEventListener('mousemove', e => {
            if (!dragging) return;

            const nx = e.clientX - ox;
            const ny = e.clientY - oy;
            x = nx;
            y = ny;

            // 更新節點位置
            nodePositions[name].x = nx;
            nodePositions[name].y = ny;

            // 移動圓和文字
            circle.setAttribute('cx', nx);
            circle.setAttribute('cy', ny);
            text.setAttribute('x', nx);
            text.setAttribute('y', ny + 4);

            // ⭐⭐ 讓所有連到這個 node 的線一起動
            lineRefs.forEach(ref => {
                const line = ref.lineEl;

                // 更新線的位置
                if (ref.gene === name) {
                    line.setAttribute('x1', nx);
                    line.setAttribute('y1', ny);
                }
                if (ref.drug === name) {
                    line.setAttribute('x2', nx);
                    line.setAttribute('y2', ny);
                }

                // ⭐⭐ 更新線的 label（中點）
                const label = line._label;
                if (label) {
                    const x1 = +line.getAttribute('x1');
                    const y1 = +line.getAttribute('y1');
                    const x2 = +line.getAttribute('x2');
                    const y2 = +line.getAttribute('y2');

                    const mx = (x1 + x2) / 2;
                    const my = (y1 + y2) / 2;

                    label.setAttribute('x', mx);
                    label.setAttribute('y', my - 4);
                }
            });

        });
    }


    function d3scalePositions(n, height, top, bottom) {
    if (n <= 1) return [height / 2];
        const usable = height - top - bottom;
        const step = usable / (n - 1);
        return Array.from({length:n}, (_, i) => top + i * step);
    }
}

/**
 * Author: Claire
 * 
 * Reformats fields taken from database for use in HTML output
 */
function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}