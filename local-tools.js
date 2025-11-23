document.addEventListener('DOMContentLoaded', function() {
    const costResult = document.getElementById('costResult');
    const currencyResult = document.getElementById('currencyResult');

    // Cost of Living Calculator
    document.getElementById('calculateCostBtn').addEventListener('click', async function() {
        costResult.textContent = 'Comparing cost of living...';
        const city1 = document.getElementById('city1').value;
        const city2 = document.getElementById('city2').value;

        // Call the Gemini API to get cost of living data
        try {
            const prompt = `Compare the general cost of living between ${city1} and ${city2}, including rent, groceries, and transport. Provide a brief, high-level summary. The response should be a string in markdown format.`;
            const payload = {
                contents: [{ parts: [{ text: prompt }] }],
            };
            const apiKey = "";
            const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=${apiKey}`;
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            const text = result?.candidates?.[0]?.content?.parts?.[0]?.text;
            if (text) {
                costResult.innerHTML = text;
            } else {
                costResult.textContent = 'Error: Could not retrieve data.';
            }
        } catch (error) {
            console.error('Error fetching cost of living data:', error);
            costResult.textContent = 'Error: Something went wrong. Please try again.';
        }
    });

    // Currency Converter
    document.getElementById('convertBtn').addEventListener('click', async function() {
        currencyResult.textContent = 'Converting currency...';
        const amount = document.getElementById('amount').value;
        const fromCurrency = document.getElementById('fromCurrency').value;
        const toCurrency = document.getElementById('toCurrency').value;
        
        // Call the Gemini API to get currency conversion
        try {
            const prompt = `Convert ${amount} ${fromCurrency} to ${toCurrency}. Provide only the converted value in a short sentence, e.g., "100 AUD is approximately 65.5 USD."`;
            const payload = {
                contents: [{ parts: [{ text: prompt }] }],
            };
            const apiKey = "";
            const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=${apiKey}`;
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            const text = result?.candidates?.[0]?.content?.parts?.[0]?.text;
            if (text) {
                currencyResult.textContent = text;
            } else {
                currencyResult.textContent = 'Error: Could not retrieve data.';
            }
        } catch (error) {
            console.error('Error fetching currency data:', error);
            currencyResult.textContent = 'Error: Something went wrong. Please try again.';
        }
    });
});
