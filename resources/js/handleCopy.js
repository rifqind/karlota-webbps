const parseTSVWithQuotes = (text) => {
    const rows = [];
    let currentRow = [];
    let currentCell = "";
    let inQuotes = false;

    for (let i = 0; i < text.length; i++) {
        const char = text[i];
        const nextChar = text[i + 1];

        if (char == '"') {
            if (inQuotes && nextChar == '"') {
                // Escaped quote
                currentCell += '"';
                i++; // skip next quote
            } else {
                inQuotes = !inQuotes; // toggle quotes
            }
        } else if (char == "\t" && !inQuotes) {
            currentRow.push(currentCell);
            currentCell = "";
        } else if ((char == "\n" || char == "\r") && !inQuotes) {
            if (currentCell || currentCell == "") {
                currentRow.push(currentCell);
                currentCell = "";
            }
            if (currentRow.length > 0) {
                rows.push(currentRow);
                currentRow = [];
            }
            if (char == "\r" && nextChar == "\n") i++; // Windows \r\n
        } else {
            currentCell += char;
        }
    }

    // Add last cell and row
    if (currentCell || currentCell == "") currentRow.push(currentCell);
    if (currentRow.length > 0) rows.push(currentRow);

    return rows;
};

export { parseTSVWithQuotes }