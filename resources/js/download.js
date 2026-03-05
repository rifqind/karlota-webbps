import * as XLSX from "xlsx";
const tableToJson = (idTabel, type = 'number', diskrepansi = false, title_name = 'Hasil Download') => {
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
        if (window.getComputedStyle(body).display != 'none') {
            const bodyRows = body.querySelectorAll('tr');
            bodyRows.forEach((row) => {
                const rowData = {};
                const cells = row.querySelectorAll('td');

                // Map cell data to headers
                cells.forEach((cell, index) => {
                    const header = headers[index] || `column_${index}`; // Fallback for missing headers
                    // let input = cell.querySelector('input')
                    let textarea = cell.querySelector('textarea')
                    // let val = input | textarea ? input.value : cell.textContent.trim(); // Trim whitespace
                    let val = textarea ? textarea.value : cell.textContent.trim(); // Trim whitespace

                    if (index != 0) { // Skip the first column
                        if (type == 'number') {
                            val = val.replace(/\./g, "").replace(/,/g, "."); // Convert German numeric format
                            if (diskrepansi) {
                                if (!isNaN(parseFloat(val))) val = parseFloat(val)
                            } else {
                                val = parseFloat(val); // Convert to a number
                                if (isNaN(val)) val = '-'
                            }
                        }
                    }
                    rowData[header] = val
                });

                // Add row data to rows array
                rows.push(rowData);
            });
        }
    });
    const title = [title_name, , , , , ,]
    const space = [, , , , , ,]
    const aoa = [
        title, space, headers, ...rows.map(row => headers.map(header => row[header] ?? ''))
    ]

    // Return the JSON object
    // let result = []
    // result.push(title, space, aoa)
    // return result;
    return aoa;
};
const theDownload = ({ setdata, title = 'Hasil Download', yCount, RULES, diskrepansi = false }) => {
    var workbook = XLSX.utils.book_new();
    Object.keys(setdata).forEach((sheetName) => {
        const data = setdata[sheetName]
        const worksheet = XLSX.utils.aoa_to_sheet(data)
        if (diskrepansi) {
        } else {
            if (sheetName == 'data') {
                if (RULES == 'Lapangan Usaha') {
                    injectTotal(worksheet, 3, 69, yCount)
                    injectSecCat(worksheet, 3, 69, yCount, RULES)
                    injectTotal(worksheet, 75, 141, yCount)
                    injectSecCat(worksheet, 75, 141, yCount, RULES)
                } else {
                    injectTotal(worksheet, 3, 20, yCount)
                    injectSecCat(worksheet, 3, 20, yCount, RULES)
                    injectTotal(worksheet, 26, 43, yCount)
                    injectSecCat(worksheet, 26, 43, yCount, RULES)
                }
            }
        }
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
    a.download = title + ".xlsx";

    // Append the link to the document and trigger the download
    document.body.appendChild(a);
    a.click();

    // Clean up
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

}

const newDownload = (setdata, title = "Hasil Download") => {
    const workbook = XLSX.utils.book_new();
    // console.log(workbook, setdata)
    Object.keys(setdata).forEach((sheetName) => {
        const payload = setdata[sheetName];

        // backward compatible: kalau payload masih AOA biasa
        const aoa = Array.isArray(payload) ? payload : payload.aoa;
        const meta = Array.isArray(payload) ? null : payload.meta;

        const worksheet = XLSX.utils.aoa_to_sheet(aoa);

        // apply formula total kalau meta ada
        if (meta?.applyTotalFormula) {
            // di buildAOAFromRowDefs kamu punya 2 header rows
            const headerRows = 2;
            const startRow = headerRows;      // 0-based
            const endRow = aoa.length - 1;

            injectTotalFormulas(worksheet, {
                startRow,
                endRow,
                yearsCount: meta.yearsCount,
                quarterCap: meta.quarterCap,
            });
        }

        XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);
    });

    const excelFile = XLSX.write(workbook, { type: "binary" });
    const blob = new Blob([s2ab(excelFile)], { type: "application/octet-stream" });

    const a = document.createElement("a");
    const url = URL.createObjectURL(blob);
    a.href = url;
    a.download = title + ".xlsx";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i < s.length; i++) {
        view[i] = s.charCodeAt(i) & 0xff;
    }
    return buf;
}
const buildRowDefsLapus = (subsectors) => {
    const defs = [];

    subsectors.forEach((ns) => {
        const catType = ns?.sector?.category?.type;
        if (catType !== "Lapangan Usaha") return;

        const catId = ns?.sector?.category_id;
        const catCode = ns?.sector?.category?.code;
        const catName = ns?.sector?.category?.name;

        const secId = ns?.sector_id ?? ns?.sector?.id;
        const secCode = ns?.sector?.code;
        const secName = ns?.sector?.name;

        const isCategoryHeader =
            ((ns.code != null && ns.code === "a" && String(secCode) === "1") ||
                (ns.code == null && String(secCode) === "1"));

        if (isCategoryHeader && catId) {
            defs.push({
                rowKey: `cat-${catId}`,
                label: `${catCode}. ${catName}`,
            });
        }

        const isSectorHeader = ns.code != null && ns.code === "a";
        if (isSectorHeader && secId) {
            defs.push({
                rowKey: `sec-${secId}`,
                label: `${secCode}. ${secName}`,
            });
        }

        if (ns.code != null && ns.id) {
            defs.push({
                rowKey: `sub-${ns.id}`,
                label: `${ns.code}. ${ns.name}`,
            });
            return;
        }

        if (ns.code == null && ns?.sector?.code != null && secId) {
            defs.push({
                rowKey: `sec-${secId}`,
                label: `${ns.sector.code}. ${ns.sector.name}`,
            });
            return;
        }

        if (ns.code == null && ns?.sector?.code == null && catId) {
            defs.push({
                rowKey: `cat-${catId}`,
                label: `${catCode}. ${ns.name}`,
            });
        }
    });

    defs.push({ rowKey: "FOOTER:PDRB", label: "PDRB" });
    defs.push({ rowKey: "FOOTER:PDRB-NonMigas", label: "PDRB Nonmigas" });
    return defs;
};

const buildRowDefsSum = (type) => {
    const defs = []
    if (type == 'Lapangan Usaha') {
        const lapusKey = ['primer', 'sekunder', 'tersier']
        lapusKey.forEach((lk) => {
            defs.push({ rowKey: lk, label: lk })
        })
    } else {
        const pengKey = ['krt', 'kap', 'pmtb', 'lainnya']
        pengKey.forEach((pK) => {
            defs.push({ rowKey: pK, label: pK })
        })
    }

    defs.push({ rowKey: "FOOTER:PDRB", label: "PDRB" });
    return defs
}

const buildRowDefsPeng = (subsectors) => {
    const defs = []
    subsectors.forEach((ns) => {
        const catType = ns?.sector?.category?.type;
        if (catType !== "Pengeluaran") return;

        const secId = ns?.sector_id ?? ns?.sector?.id;
        const secCode = ns?.sector?.code;
        const secName = ns?.sector?.name;

        const isSectorHeader = ns.code != null && ns.code === "a";
        if (isSectorHeader && secId) {
            defs.push({
                rowKey: `sec-${secId}`,
                label: `${secCode}. ${secName}`,
            });
        }

        if (ns.code != null && ns.id) {
            defs.push({
                rowKey: `sub-${ns.id}`,
                label: `${ns.code}. ${ns.name}`,
            });
            return;
        }

        if (ns.code == null && ns?.sector?.code != null && secId) {
            defs.push({
                rowKey: `sec-${secId}`,
                label: `${ns.sector.code}. ${ns.sector.name}`,
            });
            return;
        }
    });
    defs.push({ rowKey: 'FOOTER:PDRB', label: 'PDRB' })
    return defs
}

const buildAOAFromRowDefs = ({ tableModel, rowDefs, years, quarterCap }) => {
    const stake = Number(quarterCap);
    const aoa = [];

    // header (sesuaikan dengan excel kamu)
    const header1 = ["Komponen"];
    const header2 = [""];

    for (const y of years) {
        header1.push(String(y), "", "", "", "");
        header2.push("Triwulan I", "Triwulan II", "Triwulan III", "Triwulan IV", "Total");
    }

    aoa.push(header1);
    aoa.push(header2);

    for (const def of rowDefs) {
        const row = [def.label];

        // Footer special case
        const isFooter = def.rowKey.startsWith("FOOTER:");
        const footerKey = isFooter ? def.rowKey.replace("FOOTER:", "") : null;
        for (const y of years.map(String)) {
            const cell = isFooter
                ? (tableModel?.footer?.[footerKey]?.[y] ?? { q: [], total: 0 })
                : (tableModel?.rows?.[def.rowKey]?.[y] ?? { q: [], total: 0 });

            const q = cell.q ?? [];
            row.push(
                Number(q[0] ?? 0),
                Number(q[1] ?? 0),
                Number(q[2] ?? 0),
                Number(q[3] ?? 0),
                Number(cell.total ?? 0)
            );
        }

        aoa.push(row);
    }
    return aoa;
}

const buildAOADiskrepansi = ({ tableModel, secondModel, rowDefs, tableColumn, quarterCap, diskrepansi = false }) => {
    const aoa = []

    const header1 = ['Komponen']
    for (const rr of tableColumn) header1.push(rr.label)
    aoa.push(header1)

    for (const def of rowDefs) {
        const row = [def.label]
        const isFooter = def.rowKey.startsWith("FOOTER:");
        const footerKey = isFooter ? def.rowKey.replace("FOOTER:", "") : null;
        for (const rr of tableColumn) {
            if (!isNaN(Number(rr.value))) {
                const cell = isFooter
                    ? (tableModel?.footer?.[footerKey]?.[rr.value] ?? { q: [], total: 0 })
                    : (tableModel?.rows?.[def.rowKey]?.[rr.value] ?? { q: [], total: 0 });
                const datas = quarterCap == 't' ? cell.total ?? [] : cell.q[Number(quarterCap) - 1] ?? []
                row.push(datas)
            } else if (isNaN(Number(rr.value))) {
                if (rr.value == 'calculate') {
                    const calculated = isFooter
                        ? (secondModel?.footer?.[footerKey] ?? { q: [], total: 0 })
                        : (secondModel?.rows?.[def.rowKey] ?? { q: [], total: 0 })
                    const datas = diskrepansi ? calculated.disk : calculated.diff
                    row.push(datas)
                } else if (rr.value == 'total') {
                    const cell = isFooter
                        ? (tableModel?.footer?.[footerKey]?.[rr.value] ?? { q: [], total: 0 })
                        : (tableModel?.rows?.[def.rowKey]?.[rr.value] ?? { q: [], total: 0 });
                    const datas = quarterCap == 't' ? cell.total ?? 0 : cell.q[Number(quarterCap) - 1] ?? 0
                    row.push(datas)
                }
            }
        }
        aoa.push(row)
    }
    return aoa
}

const injectTotalFormulas = (ws, { startRow, endRow, yearsCount, quarterCap }) => {
    const stake = Number(quarterCap);      // 1..4
    const stride = 5;                      // Q1..Q4 + Total
    const baseCol = 1;                     // B (0-based). A=Komponen

    for (let r = startRow; r <= endRow; r++) {
        for (let y = 0; y < yearsCount; y++) {
            const q1Col = baseCol + y * stride;
            const qLastCol = q1Col + (stake - 1);
            const totalCol = q1Col + 4;

            const q1Addr = XLSX.utils.encode_cell({ r, c: q1Col });
            const qLastAddr = XLSX.utils.encode_cell({ r, c: qLastCol });
            const totalAddr = XLSX.utils.encode_cell({ r, c: totalCol });

            ws[totalAddr] = ws[totalAddr] || { t: "n", v: 0 };
            ws[totalAddr].t = "n";
            ws[totalAddr].f = `SUM(${q1Addr}:${qLastAddr})`;
        }
    }
};

const injectTotal = (ws, start, end, yCount) => {
    const baseCol = 1
    const byBR = 2
    for (let r = start; r <= end; r++) {
        for (let y = 0; y < yCount; y++) {
            //total
            const q1Col = baseCol + (y * 5)
            const q2Col = q1Col + 1
            const q3Col = q1Col + 2
            const q4Col = q1Col + 3
            const tCol = q1Col + 4
            const qData = [q1Col, q2Col, q3Col, q4Col]

            const q1Addr = XLSX.utils.encode_cell({ r, c: q1Col })
            const q4Addr = XLSX.utils.encode_cell({ r, c: q4Col })
            const tAddr = XLSX.utils.encode_cell({ r, c: tCol })
            // console.log(q1Addr, q4Addr, tAddr)
            ws[tAddr].t = 'n'
            ws[tAddr].f = `SUM(${q1Addr}:${q4Addr})`

        }
    }
}
const ensureCell = (ws, addr) => (ws[addr] ??= { t: "n", v: 0 });
const cell = (r, c) => XLSX.utils.encode_cell({ r, c });
const OFFSET_LAPUS = [0, 72]
const OFFSET_PENG = [0, 23]
const injectSecCat = (ws, start, end, yCount, RULES) => {
    let rules = RULES == 'Lapangan Usaha' ? RULES_LAPUS : RULES_PENG
    let offset = RULES == 'Lapangan Usaha' ? OFFSET_LAPUS : OFFSET_PENG
    const baseCol = 1
    for (const off of offset) {
        for (let y = 0; y < yCount; y++) {
            const q1Col = baseCol + (y * 5)
            const q2Col = q1Col + 1
            const q3Col = q1Col + 2
            const q4Col = q1Col + 3
            const tCol = q1Col + 4
            const qData = [q1Col, q2Col, q3Col, q4Col]
            for (const [targetStr, expr] of Object.entries(rules)) {
                const target = Number(targetStr) + off
                for (const qq of qData) {
                    const addr = cell(target, qq);
                    ensureCell(ws, addr).t = "n";
                    ws[addr].f = buildFormula(expr, qq, off);
                }
            }
        }
    }
}
const RULES_LAPUS = {
    3: "4+12+13",
    4: "5:11",
    14: "15:18",
    19: "20+23:37",
    20: "21:22",
    38: "39:40",
    43: "44:45",
    46: "47:52",
    53: "54:55",
    57: "58:61",

    // yang kamu minta (PDRB-like)
    68: "3+14+19+38+41+42+43+46+53+56+57+62:67",

    // selisih
    69: "68-15",
};
const RULES_PENG = {
    3: "4:10",
    13: "14:15",
    17: "18-19"
}
const buildFormula = (expr, col, off = 0) => {
    const s = String(expr).replace(/\s+/g, "");

    const partToRef = (p) => {
        if (p.includes(":")) {
            const [a, b] = p.split(":").map(Number);
            return `${cell(a + off, col)}:${cell(b + off, col)}`;
        }
        return cell(Number(p) + off, col);
    };

    const sumExpr = (str) => {
        const parts = str.split("+").filter(Boolean);
        const args = parts.map((p) => partToRef(p));
        return `SUM(${args.join(",")})`;
    };

    if (s.includes("-")) {
        const [L, R] = s.split("-");
        const left = L.includes("+") || L.includes(":") ? sumExpr(L) : partToRef(L);
        const right = R.includes("+") || R.includes(":") ? sumExpr(R) : partToRef(R);
        return `${left}-${right}`;
    }

    // pure sum
    return sumExpr(s);
};


export { tableToJson, theDownload, buildRowDefsLapus, buildAOAFromRowDefs, buildRowDefsPeng, newDownload, buildRowDefsSum, buildAOADiskrepansi } 