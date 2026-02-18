const getData = (subsectors, quarter, yr) => {
  // const theData = dataHere.value.find((x) => {
  //   return x.quarter == quarter && x.subsector_id == subsectors;
  // });
  const theData = seriesOfData(yr).find((x) => {
    return x.quarter == quarter && x.subsector_id == subsectors;
  });
  if (theData) {
    let formattedResult;
    formattedResult =
      theData[props.type] == "" || theData[props.type] == null
        ? null
        : formatNumberGerman(Number(theData[props.type]), 0, 9);
    return formattedResult;
  }
};
const lvlOne = ref({});
const getSumLvlOne = (value, quarter, yr) => {
  // Get all subsector IDs related to the given sector_id (value)
  let subsectorIds = props.subsectors
    .filter((x) => x.sector_id == value)
    .map((x) => x.id);
  // Get all matching data where quarter matches and subsector_id is in the subsector list

  // const filteredData = dataHere.value.filter(
  //   (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  // );
  const filteredData = seriesOfData(yr).filter(
    (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  );
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlOne.value[value]) lvlOne.value[value] = {};
  lvlOne.value[value][quarter] = result;

  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const lvlTwo = ref({});
const getSumLvlTwo = (value, quarter, yr) => {
  let subsectorIds = props.subsectors
    .filter((x) => x.sector.category_id == value)
    .map((x) => x.id);
  // const filteredData = dataHere.value.filter(
  //   (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  // );
  const filteredData = seriesOfData(yr).filter(
    (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  );
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlTwo.value[value]) lvlTwo.value[value] = {};
  lvlTwo.value[value][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumTotalFromVal = (value, yr) => {
  // const filteredData = dataHere.value.filter((x) => x.subsector_id == value);
  const filteredData = seriesOfData(yr).filter((x) => x.subsector_id == value);
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  // console.log(result);
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumRowCat = (value, yr) => {
  if (!lvlTwo.value[value]) return 0; // If no data, return 0

  // Get all quarter sums for this category
  let totalSum = Object.values(lvlTwo.value[value]).reduce(
    (sum, quarterSum) => sum + quarterSum,
    0
  );

  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};

const getSumRowSector = (value, yr) => {
  if (!lvlOne.value[value]) return 0; // If no data, return 0

  // Get all quarter sums for this category
  let totalSum = Object.values(lvlOne.value[value]).reduce(
    (sum, quarterSum) => sum + quarterSum,
    0
  );

  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};

const lvlPDRB = ref({});
const getPDRB = (quarter, yr) => {
  // const filteredData = dataHere.value.filter((x) => x.quarter == quarter);
  const filteredData = seriesOfData(yr).filter((x) => x.quarter == quarter);
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB"]) lvlPDRB.value["PDRB"] = {};
  lvlPDRB.value["PDRB"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getPDRBNonMigas = (quarter, yr) => {
  // const filteredData = dataHere.value.filter(
  //   (x) => x.quarter == quarter && ![10, 15].includes(Number(x.subsector_id))
  // );
  const filteredData = seriesOfData(yr).filter(
    (x) => x.quarter == quarter && ![10, 15].includes(Number(x.subsector_id))
  );
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB-NonMigas"]) lvlPDRB.value["PDRB-NonMigas"] = {};
  lvlPDRB.value["PDRB-NonMigas"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumPDRB = (pdrb, yr) => {
  if (!lvlPDRB.value[pdrb]) return 0;

  let totalSum = Object.values(lvlPDRB.value[pdrb]).reduce(
    (sum, pdrbSum) => sum + pdrbSum,
    0
  );
  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};




//
const getData = (type, quarter) => {
  const theData = dataHere.value.find((x) => {
    return x.quarter == quarter && x.setdata == type;
  });
  if (theData) {
    let formattedResult;
    formattedResult =
      theData[props.type] == "" || theData[props.type] == null
        ? null
        : formatNumberGerman(Number(theData[props.type]), 0, 9);
    return formattedResult;
  }
};
const lvlPDRB = ref({});
const getPDRB = (quarter) => {
  const filteredData = dataHere.value.filter((x) => x.quarter == quarter);
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB"]) lvlPDRB.value["PDRB"] = {};
  lvlPDRB.value["PDRB"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const getSumPDRB = (pdrb) => {
  if (!lvlPDRB.value[pdrb]) return 0;

  let totalSum = Object.values(lvlPDRB.value[pdrb]).reduce(
    (sum, pdrbSum) => sum + pdrbSum,
    0
  );
  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};
const getSumTotalFromVal = (value) => {
  const filteredData = dataHere.value.filter((x) => x.setdata == value);
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  // console.log(result);
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};