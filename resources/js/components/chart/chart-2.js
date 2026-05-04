
export const initChartTwo = () => {
    const chartElement = document.querySelector('#chartTwo');

    if (chartElement) {
        const paymentSeries = [42000, 27000, 18000, 13000];
        const paymentLabels = ["Virement", "Espèces", "Chèque", "Mobile Money"];

        const chartTwoOptions = {
            series: paymentSeries,
            colors: ["#465FFF", "#0EA5E9", "#22C55E", "#F59E0B"],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "donut",
                width: 360,
                height: 360,
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: "72%",
                        labels: {
                            show: false,
                            name: {
                                fontSize: "16px",
                                color: "#64748B",
                            },
                            value: {
                                fontSize: "26px",
                                fontWeight: 600,
                                color: "#1D2939",
                                formatter: function (val, opts) {
                                    return opts.w.globals.seriesPercent[opts.seriesIndex].toFixed(0) + "%";
                                },
                            },
                            total: {
                                show: false,
                                label: "Total",
                                color: "#64748B",
                                formatter: function (w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return total.toLocaleString('fr-FR') + " FCFA";
                                },
                            },
                        },
                    },
                },
            },
            labels: paymentLabels,
            fill: {
                type: "gradient",
                gradient: {
                    shade: "light",
                    type: "vertical",
                    shadeIntensity: 0.35,
                    inverseColors: false,
                    opacityFrom: 0.85,
                    opacityTo: 0.85,
                },
            },
            legend: {
                position: "bottom",
                horizontalAlign: "center",
                floating: false,
                offsetY: 0,
                fontSize: "14px",
                formatter: function (seriesName, opts) {
                    const value = paymentSeries[opts.seriesIndex];
                    return `${seriesName} : ${value.toLocaleString('fr-FR')} FCFA`;
                },
                labels: {
                    colors: "#475569",
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 6,
                },
            },
            stroke: {
                width: 0,
            },
        };

        const chart = new ApexCharts(chartElement, chartTwoOptions);
        chart.render().then(() => {
            const legendEl = chartElement.querySelector('.apexcharts-legend');
            if (legendEl) {
                legendEl.style.justifyContent = 'center';
                legendEl.style.width = '100%';
            }
        });

        const total = paymentSeries.reduce((sum, value) => sum + value, 0);
        const totalElement = document.querySelector('#chartTwoTotal');
        if (totalElement) {
            totalElement.textContent = `Total global : ${total.toLocaleString('fr-FR')} FCFA`;
        }

        return chart;
    }
}

export default initChartTwo;
