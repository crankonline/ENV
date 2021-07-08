let w = window;
let d = document;

let elements = {
    passport: {
        series: 'eSeries',
        seriesCheckbox: 'eSeries-checkbox',
        number: 'eNumber'
    },
    search: {
        series: 'sSeries',
        seriesCheckbox: 'sSeries-checkbox',
        number: 'sNumber'
    }
};

let loadElement = (element) => d.getElementById(element);
let seriesCheckboxOnClick = (pointer) => {
    let Checked = (series, seriesCheckbox) => {
        series.readOnly = !seriesCheckbox.checked;
        series.value = !seriesCheckbox.checked ? 'N/A' : '';
    }
    (pointer === 'search') ?
        Checked(elements.search.series, elements.search.seriesCheckbox) :
        Checked(elements.passport.series, elements.passport.seriesCheckbox);
}
let setDefaultValues = (series, seriesCheckbox) => {
    series.readOnly = ['N/A'].includes(series.value);
    seriesCheckbox.checked = !series.readOnly;
}

w.addEventListener('load', function () {
    elements.search.series = loadElement(elements.search.series);
    elements.search.seriesCheckbox = loadElement(elements.search.seriesCheckbox);
    setDefaultValues(elements.search.series, elements.search.seriesCheckbox)

    elements.passport.series = loadElement(elements.passport.series);
    elements.passport.seriesCheckbox = loadElement(elements.passport.seriesCheckbox);
    if (elements.passport.series) {
        setDefaultValues(elements.passport.series, elements.passport.seriesCheckbox);
    }
});

