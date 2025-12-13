/**
 * PDF Saver
 * Author: Evan
 * 
 * Given input given in HTML formats, Outputs a report
 */
export async function mdToPdfSelectable(mdText, filename = "output.pdf", title = "Report", logoUrl = null) {
    const { jsPDF } = window.jspdf;
    const md = window.markdownit({ html: true });
    const html = md.render(mdText);

    // Temporary container
    const container = document.createElement("div");
    container.innerHTML = html;
    document.body.appendChild(container);

    const pdf = new jsPDF({ unit: "pt", format: "a4", compress: true });
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const margin = 40;
    let y = margin;

    pdf.setFont("times", "normal");
    pdf.setFontSize(12);

    // Insert logo
    if (logoUrl) {
        try {
            const img = await loadImageAsDataUrl(logoUrl);
            pdf.addImage(img, "PNG", margin, y - 10, 100, 80);
        } catch (err) {
            console.warn("Failed to load logo:", err);
        }
    }

    // Insert Title
    const titleY = y + (logoUrl ? 30 : 0);
    pdf.setFontSize(20);
    pdf.setFont("times", "bold");
    pdf.text(title, pageWidth / 2, titleY, { align: "center" });

    y = titleY + 40;

    // ---- NEW: extract styled lines from HTML ----
    const elements = [...container.childNodes];
    container.remove();

    for (let el of elements) {
        const blocks = extractStyledBlocks(el);

        for (let block of blocks) {
            // Handle headings
            if (block.type === "heading") {

                // add spacing before new header
                y += 20;

                const sizeMap = { h1: 20, h2: 18, h3: 16, h4: 14 };
                const size = sizeMap[block.tag] || 14;

                pdf.setFont("times", "bold");
                pdf.setFontSize(size);

                if (y + size > pageHeight - margin) { pdf.addPage(); y = margin; }
                pdf.text(block.text, margin, y);

                
                y += size + 6;

                // Reset default
                pdf.setFont("times", "normal");
                pdf.setFontSize(12);
                continue;
            }

            // Handle paragraph content (styled chunks)
            const wrapped = wrapRichText(pdf, block.chunks, pageWidth - 2 * margin);

            for (let line of wrapped) {
                if (y + 16 > pageHeight - margin) {
                    pdf.addPage();
                    y = margin;
                }
                renderRichLine(pdf, line, margin, y);
                y += 16;
            }
            y += 4; // extra spacing between paragraphs
        }
    }

    pdf.save(filename);

    // -----------------------------
    // NEW: Extract styled blocks
    // -----------------------------

    function extractStyledBlocks(node) {
        let blocks = [];

        if (node.nodeType === 3) {
            // text node
            const text = node.textContent.trim();
            if (text) {
                blocks.push({
                    type: "paragraph",
                    chunks: [{ text, bold: false, italic: false, underline: false }]
                });
            }
            return blocks;
        }

        if (node.nodeType !== 1) return blocks;

        const tag = node.tagName.toLowerCase();

        // Headings
        if (/h[1-4]/.test(tag)) {
            blocks.push({
                type: "heading",
                tag,
                text: node.textContent.trim()
            });
       
            return blocks;
        }

        // Paragraph-level elements
        if (["p", "div", "li"].includes(tag)) {
            blocks.push({
                type: "paragraph",
                chunks: extractStyledChunks(node)
            });
            return blocks;
        }

        // Fallback: recursive parsing
        node.childNodes.forEach(child => {
            blocks.push(...extractStyledBlocks(child));
        });

        return blocks;
    }

    // Parse inline <b>, <i>, <u>, <strong>, <em>
    function extractStyledChunks(node, inherited = {}) {
        let style = {
            bold: inherited.bold || ["b", "strong"].includes(node.tagName?.toLowerCase()),
            italic: inherited.italic || ["i", "em"].includes(node.tagName?.toLowerCase()),
            underline: inherited.underline || ["u"].includes(node.tagName?.toLowerCase())
        };

        let chunks = [];

        node.childNodes.forEach(child => {
            if (child.nodeType === 3) {
                const text = child.textContent;
                if (text.trim()) {
                    chunks.push({ text, ...style });
                }
            } else if (child.nodeType === 1) {
                chunks.push(...extractStyledChunks(child, style));
            }
        });

        return chunks;
    }

    // ------------------------------
    // Rich text wrapping & rendering
    // ------------------------------

    function wrapRichText(pdf, chunks, maxWidth) {
        let lines = [];
        let currLine = [];
        let currWidth = 0;

        for (let chunk of chunks) {
            const words = chunk.text.split(/\s+/);

            for (let w of words) {
                const width = pdf.getTextWidth(w + " ");

                if (currWidth + width > maxWidth) {
                    lines.push(currLine);
                    currLine = [];
                    currWidth = 0;
                }

                currLine.push({ ...chunk, text: w });
                currWidth += width;
            }
        }

        if (currLine.length) lines.push(currLine);
        return lines;
    }

    function renderRichLine(pdf, lineChunks, x, y) {
        let offset = x;

        for (let chunk of lineChunks) {
            pdf.setFont(
                "times",
                chunk.bold ? "bold" :
                chunk.italic ? "italic" :
                "normal"
            );
            if (chunk.underline) {
                pdf.text(chunk.text, offset, y, { underline: true });
            } else {
                pdf.text(chunk.text, offset, y);
            }

            offset += pdf.getTextWidth(chunk.text + " ");
        }

        // reset
        pdf.setFont("times", "normal");
    }

    function loadImageAsDataUrl(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => {
                const canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;
                canvas.getContext("2d").drawImage(img, 0, 0);
                resolve(canvas.toDataURL("image/png"));
            };
            img.onerror = reject;
            img.src = url;
        });
    }
}
