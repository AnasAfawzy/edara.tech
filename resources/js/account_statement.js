document.addEventListener('DOMContentLoaded', function () {
    const statementForm = document.getElementById('statementForm');
    const accountNameInput = document.getElementById('account_name');
    const accountIdInput = document.getElementById('account_id');
    const accountsDatalist = document.getElementById('accounts');
    const statementResultsDiv = document.getElementById('statementResults');

    // Function to initialize journal entry modal listeners
    function initializeJournalEntryModalListeners() {
        const journalEntryModalElement = document.getElementById('journalEntryModal');
        if (!journalEntryModalElement) {
            // Modal element not found, likely not on a page with the modal
            return;
        }
        const journalEntryModal = new bootstrap.Modal(journalEntryModalElement);

        document.querySelectorAll('.view-journal-entry').forEach(button => {
            button.removeEventListener('click', handleJournalEntryClick); // Prevent duplicate listeners
            button.addEventListener('click', handleJournalEntryClick);
        });

        function handleJournalEntryClick() {
            const entryId = this.dataset.id;
            const journalEntryContent = document.getElementById('journalEntryContent');

            // Show loading indicator
            journalEntryContent.innerHTML =
                `<div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;

            // Fetch journal entry details
            fetch(`/journal-entries/${entryId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    journalEntryContent.innerHTML = html;
                    journalEntryModal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    journalEntryContent.innerHTML =
                        `<div class="text-center py-5 text-danger">
                    Failed to load journal entry details
                </div>`;
                    journalEntryModal.show();
                });
        }
    }

    // Set account_id hidden input when an account is selected from datalist
    accountNameInput.addEventListener('input', function () {
        const selectedOption = Array.from(accountsDatalist.options).find(option => option.value === this.value);
        if (selectedOption) {
            accountIdInput.value = selectedOption.dataset.id;
        } else {
            accountIdInput.value = ''; // Clear if no valid selection
        }
    });

    if (statementForm) {
        statementForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            const accountId = accountIdInput.value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const formAction = statementForm.dataset.action; // Get the action URL from data-action attribute

            // if (!accountId) {
            //     alert('Please select a valid account from the list.');
            //     return;
            // }

            // Show a loading indicator
            statementResultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';

            fetch(formAction, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        account_id: accountId,
                        start_date: startDate,
                        end_date: endDate
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        // If response is not OK (e.g., 404, 500), parse error message
                        return response.json().then(errorData => {
                            throw new Error(errorData.error || 'Something went wrong!');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.details) {
                        statementResultsDiv.innerHTML = data.details;
                        initializeJournalEntryModalListeners(); // Initialize listeners after content is loaded
                    } else {
                        statementResultsDiv.innerHTML = '<div class="alert alert-warning">No statement data received.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statementResultsDiv.innerHTML = `<div class="alert alert-danger">Error loading statement: ${error.message}</div>`;
                });
        });
    }

    // Initialize listeners on initial page load if the modal elements are present
    initializeJournalEntryModalListeners();
});