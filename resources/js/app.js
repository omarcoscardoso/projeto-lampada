import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const calendarElement = document.getElementById('calendar');
    const devotionalContent = document.getElementById('devotional-content'); // Target the new inner div
    const shareButton = document.getElementById('whatsapp-share-btn');
    let currentDevotional = null; // To hold the current devotional data

    // Helper to strip HTML tags
    function stripHtml(html) {
        let tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || "";
    }

    if (calendarElement && devotionalContent && shareButton) {
        // Setup share button listener once
        shareButton.addEventListener('click', () => {
            if (!currentDevotional) return;

            const message = `
*${currentDevotional.reference_old_testament}*
${stripHtml(currentDevotional.content_old_testament)}

*${currentDevotional.reference_new_testament}*
${stripHtml(currentDevotional.content_new_testament)}
            `;
            const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(message.trim())}`;
            window.open(whatsappUrl, '_blank');
        });

        const calendar = new VanillaCalendar(calendarElement, {
            settings: {
                lang: 'pt',
                visibility: {
                    theme: 'system',
                },
            },
            actions: {
                clickDay(event, self) {
                    if (self.selectedDates && self.selectedDates[0]) {
                        const dateString = self.selectedDates[0];
                        const selectedDate = new Date(dateString + 'T00:00:00');
                        const yyyy = selectedDate.getFullYear();
                        const mm = String(selectedDate.getMonth() + 1).padStart(2, '0');
                        const dd = String(selectedDate.getDate()).padStart(2, '0');
                        const formattedDate = `${yyyy}-${mm}-${dd}`;
                        fetchDevotional(formattedDate);
                    }
                },
            },
        });

        calendar.init();

        async function fetchDevotional(date) {
            devotionalContent.innerHTML = '<p class="text-gray-600 dark:text-gray-400">Carregando devocional...</p>';
            shareButton.classList.add('hidden');
            currentDevotional = null;

            try {
                const response = await fetch(`/api/devotionals/${date}`);
                const data = await response.json();

                if (response.ok) {
                    currentDevotional = data; // Store data
                    devotionalContent.innerHTML = `
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">${data.reference_old_testament}</h3>
                        ${data.content_old_testament}
                        <hr class="my-6 border-gray-200 dark:border-gray-600">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">${data.reference_new_testament}</h3>
                        ${data.content_new_testament}
                    `;
                    shareButton.classList.remove('hidden'); // Show button
                } else {
                    devotionalContent.innerHTML = `<p class="text-gray-600 dark:text-gray-400">${data.message}</p>`;
                }
            } catch (error) {
                console.error('Error fetching devotional:', error);
                devotionalContent.innerHTML = '<p class="text-red-500">Erro ao carregar o devocional.</p>';
            }
        }

        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const formattedToday = `${yyyy}-${mm}-${dd}`;
        fetchDevotional(formattedToday);
    }
});
