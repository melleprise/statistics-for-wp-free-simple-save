//	include/delete-warning.js

// console.log("delete-warning.js is loaded");

document.addEventListener('DOMContentLoaded', function () {
    const deactivateLink = document.querySelector('#deactivate-wp-statistics-free-simple-easy');

    if (deactivateLink) {
        deactivateLink.addEventListener('click', function (event) {
            event.preventDefault();

            document.body.style.overflow = 'hidden';

            const modalOverlay = document.createElement('div');
            modalOverlay.classList.add('modal-overlay');

            const modalContent = document.createElement('div');
            modalContent.classList.add('modal-content');

            const closeButton = document.createElement('button');
            closeButton.classList.add('modal-close');
            closeButton.innerHTML = '&times;';
            closeButton.addEventListener('click', function () {
                document.body.style.overflow = '';
                modalOverlay.remove();
            });

            const title = document.createElement('h2');
            title.classList.add('modal-title');
            title.textContent = 'Warning';

            const description = document.createElement('p');
            description.classList.add('modal-description');
            description.innerHTML = `
                Deactivating this plugin will not delete your data.<br><br> 
                However, if you choose to <span class="highlight">delete the plugin</span> later,<br>
                all associated data will be <span class="highlight">permanently lost</span>.
            `;

            const buttonContainer = document.createElement('div');
            buttonContainer.classList.add('button-container');

            const confirmButton = document.createElement('button');
            confirmButton.classList.add('button', 'button-confirm');
            confirmButton.textContent = 'Deactivate';
            confirmButton.addEventListener('click', function () {
                document.body.style.overflow = '';
                window.location.href = deactivateLink.href;
            });

            const cancelButton = document.createElement('button');
            cancelButton.classList.add('button', 'button-cancel');
            cancelButton.textContent = 'Cancel';
            cancelButton.addEventListener('click', function () {
                document.body.style.overflow = '';
                modalOverlay.remove();
            });

            buttonContainer.appendChild(cancelButton);
            buttonContainer.appendChild(confirmButton);

            modalContent.appendChild(closeButton);
            modalContent.appendChild(title);
            modalContent.appendChild(description);
            modalContent.appendChild(buttonContainer);
            modalOverlay.appendChild(modalContent);

            document.body.appendChild(modalOverlay);
        });
    }
});

// CSS Styles (should be included in a stylesheet)
const style = document.createElement('style');
style.textContent = `
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.75);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .modal-content {
        background-color: #fff;
        padding: 40px;
        width: 500px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-align: center;
        position: relative;
    }
    .modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 20px;
        background: none;
        border: none;
        cursor: pointer;
        color: #999;
    }
    .modal-close:hover {
        color: #333;
    }
    .modal-title {
        font-size: 1.5em;
        color: #333;
        margin-bottom: 20px;
    }
    .modal-description {
        font-size: 1.2em;
        color: #666;
    }
    .highlight {
        color: #d9534f;
    }
    .button-container {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        justify-content: center;
    }
    .button {
        padding: 10px 20px;
        font-size: 1em;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .button-confirm {
        background-color: #0073aa;
        color: #fff;
    }
    .button-cancel {
        background-color: #757575;
        color: #fff;
    }
`;
document.head.appendChild(style);
