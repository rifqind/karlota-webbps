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
const theDownload = (setdata, title = 'Hasil Download', yCount) => {
    var workbook = XLSX.utils.book_new();
    Object.keys(setdata).forEach((sheetName) => {
        const data = setdata[sheetName]
        console.log(data)
        const worksheet = XLSX.utils.aoa_to_sheet(data)
        // worksheet['F3'] = { t: 'n', f: 'SUM(B3:E3)' }
        injectTotal(worksheet, 3, 69, yCount)
        injectSecCat(worksheet, 3, 69, yCount)
        injectTotal(worksheet, 75, 141, yCount)
        injectSecCat(worksheet, 75, 141, yCount)
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
    console.log(workbook, setdata)
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

    //   console.log(defs)
    return defs;
};

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

            // if (r == 3) {
            //     let rNeed = [4, 12, 13]
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 3, c: qq })
            //         ws[tAddr].t = 'n'
            //         let rAddr = []
            //         for (const rr of rNeed) {
            //             const qAddr = XLSX.utils.encode_cell({ r: rr, c: qq })
            //             rAddr.push(qAddr)
            //         }
            //         ws[tAddr].f = `${rAddr[0]}+${rAddr[1]}+${rAddr[2]}`
            //     }
            // }
            // if (r == 4) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 4, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 5, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 11, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 14) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 14, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 15, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 18, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 19) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 19, c: qq })
            //         ws[tAddr].t = 'n'
            //         const rAddr = XLSX.utils.encode_cell({ r: 20, c: qq })
            //         const r1Addr = XLSX.utils.encode_cell({ r: 23, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 37, c: qq })
            //         ws[tAddr].f = `${rAddr}+SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 20) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 20, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 21, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 22, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 38) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 38, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 39, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 40, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 43) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 43, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 44, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 45, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 46) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 46, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 47, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 52, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 53) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 53, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 54, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 55, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 57) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 57, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 58, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 61, c: qq })
            //         ws[tAddr].f = `SUM(${r1Addr}:${rLAddr})`
            //     }
            // }
            // if (r == 68) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 68, c: qq });
            //         ws[tAddr].t = "n";
            //         const parts = [3, 14, 19, 38, 41, 42, 43, 46, 53, 56, 57];
            //         const refs = parts.map((rr) => XLSX.utils.encode_cell({ r: rr, c: qq }));
            //         const r1Addr = XLSX.utils.encode_cell({ r: 62, c: qq });
            //         const rLAddr = XLSX.utils.encode_cell({ r: 67, c: qq });
            //         ws[tAddr].f = `${refs.join("+")}+SUM(${r1Addr}:${rLAddr})`;
            //     }
            // }
            // if (r == 69) {
            //     for (const qq of qData) {
            //         const tAddr = XLSX.utils.encode_cell({ r: 69, c: qq })
            //         ws[tAddr].t = 'n'
            //         const r1Addr = XLSX.utils.encode_cell({ r: 68, c: qq })
            //         const rLAddr = XLSX.utils.encode_cell({ r: 15, c: qq })
            //         ws[tAddr].f = `${r1Addr}-${rLAddr}`
            //     }
            // }
        }
    }
}
const ensureCell = (ws, addr) => (ws[addr] ??= { t: "n", v: 0 });
const cell = (r, c) => XLSX.utils.encode_cell({ r, c });
const OFFSET = [0, 72]
const injectSecCat = (ws, start, end, yCount) => {
    const baseCol = 1
    for (const off of OFFSET) {
        for (let y = 0; y < yCount; y++) {
            const q1Col = baseCol + (y * 5)
            const q2Col = q1Col + 1
            const q3Col = q1Col + 2
            const q4Col = q1Col + 3
            const tCol = q1Col + 4
            const qData = [q1Col, q2Col, q3Col, q4Col]
            for (const [targetStr, expr] of Object.entries(RULES)) {
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
const RULES = {
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


export { tableToJson, theDownload, buildRowDefsLapus, buildAOAFromRowDefs, newDownload } 