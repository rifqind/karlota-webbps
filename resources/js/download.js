import * as XLSX from "xlsx";
const tableToJson = (idTabel) => {
    const table = document.getElementById(idTabel);
    if (!table) {
        console.error(`Table with id "${idTabel}" not found.`);
        return null;
    }

    const headers = [];
    const rows = [];

    // Extract headers from thead
    const headerRows = table.querySelector('thead tr');
    if (headerRows) {
        const headersTh = headerRows.querySelectorAll('th');
        headersTh.forEach((th) => {
            headers.push(th.textContent.trim()); // Trim whitespace
        });
    }

    // Extract rows from tbody
    const tBody = table.querySelectorAll('tbody');
    tBody.forEach((body) => {
        // Check if the body is visible
        if (window.getComputedStyle(body).display !== 'none') {
            const bodyRows = body.querySelectorAll('tr');
            bodyRows.forEach((row) => {
                const rowData = {};
                const cells = row.querySelectorAll('td');

                // Map cell data to headers
                cells.forEach((cell, index) => {
                    const header = headers[index] || `column_${index}`; // Fallback for missing headers
                    let val = cell.textContent.trim(); // Trim whitespace

                    if (index !== 0) { // Skip the first column
                        val = val.replace(/\./g, "").replace(/,/g, "."); // Convert German numeric format
                        val = parseFloat(val); // Convert to a number
                        if (isNaN(val)) val = '-'
                    }
                    rowData[header] = val
                });

                // Add row data to rows array
                rows.push(rowData);
            });
        }
    });
    const aoa = [
        headers, ...rows.map(row => headers.map(header => row[header] ?? ''))
    ]

    // Return the JSON object
    return aoa;
};
const theDownload = (setdata) => {
    var workbook = XLSX.utils.book_new();
    Object.keys(setdata).forEach((sheetName)=>{
        const data = setdata[sheetName]
        const worksheet = XLSX.utils.aoa_to_sheet(data)
        XLSX.utils.book_append_sheet(workbook, worksheet, sheetName)
    })
    // Convert the workbook to a binary Excel file
    var excelFile = XLSX.write(workbook, { type: "binary" });

    // Convert the binary Excel file to a Blob
    var blob = new Blob([s2ab(excelFile)], {
        type: "application/octet-stream",
    });

    // Create a download link
    var a = document.createElement("a");
    var url = URL.createObjectURL(blob);
    a.href = url;
    a.download = "result.xlsx";

    // Append the link to the document and trigger the download
    document.body.appendChild(a);
    a.click();

    // Clean up
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

}
function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i < s.length; i++) {
        view[i] = s.charCodeAt(i) & 0xff;
    }
    return buf;
}
export { tableToJson, theDownload } 