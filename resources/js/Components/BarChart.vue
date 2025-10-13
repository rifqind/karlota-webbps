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
import ChartDataLabels from "chartjs-plugin-datalabels";
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ChartDataLabels
);
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
  plugins: {
    datalabels: {
      color: "#444", // Color of the text (e.g., dark gray)
      anchor: "end", // Anchor the label to the end of the bar
      align: "end", // Align the label text (for horizontal bar, 'end' is usually outside)
      offset: 4, // Small offset to move it slightly outside the bar
      font: {
        weight: "bold",
      },
      formatter: (value, context) => {
        // You can format the value here (e.g., add currency, percentage, etc.)
        return value.toLocaleString();
      },
    },
    // IMPORTANT: Hide the default Chart.js tooltip since we are using datalabels
    tooltip: {
      enabled: true,
    },
  },
  scales: {
    x: {
      // Set max value or padding to ensure the label is not clipped
      suggestedMax: Math.max(...props.chartValue) * 1.1, // Adds 10% space
    },
  },
};
</script>
<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>
