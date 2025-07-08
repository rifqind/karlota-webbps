<script setup>
const props = defineProps({
  chartLabel: {
    type: Array,
    required: true,
  },
  chartValue: {
    type: Array,
    required: true,
  },
  legend: {
    type: String,
    required: false,
    default: "Grafik",
  },
});
import { Bar } from "vue-chartjs";
import { defineComponent } from "vue";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from "chart.js";
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);
defineComponent({
  Bar,
});
const colorCount = props.chartValue.length;
const getRandomColor = () => {
  const r = Math.floor(Math.random() * 200);
  const g = Math.floor(Math.random() * 200);
  const b = Math.floor(Math.random() * 200);
  return `rgba(${r}, ${g}, ${b}, 0.7)`;
};
const backgroundColors = Array.from({ length: colorCount }, getRandomColor);
const borderColors = backgroundColors.map((c) => c.replace("0.7", "1"));
const chartData = {
  labels: props.chartLabel,
  datasets: [
    {
      label: props.legend,
      data: props.chartValue,
      backgroundColor: backgroundColors,
      borderColor: borderColors,
      borderWidth: 1,
      fill: false,
      axis: "y",
    },
  ],
};
const chartOptions = {
  maintainAspectRatio: true,
  responsive: true,
  indexAxis: "y",
};
</script>
<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>
