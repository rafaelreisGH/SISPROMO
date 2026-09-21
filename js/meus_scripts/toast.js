/**
 * toast.js - Módulo centralizado de notificações Toast (Bootstrap 5)
 *
 * Objetivo: Fornecer uma função global `showToast()` reutilizável em todas as
 * Views que utilizam Bootstrap 5. Evita a duplicação do mesmo código de toast
 * em múltiplos arquivos de View.
 *
 * @param {string} mensagem - Texto a ser exibido na notificação.
 * @param {string} tipo     - Tipo visual do toast: 'success', 'danger', 'warning', 'info'.
 * @param {number} duracao  - Duração em milissegundos antes do auto-fechamento (padrão: 4000).
 */

/**
 * Garante que o container de toasts existe no DOM.
 * Ele é criado uma única vez e reutilizado em chamadas subsequentes.
 *
 * @returns {HTMLElement} O elemento container de toasts.
 */
function getToastContainer() {
    let container = document.getElementById('toast-container-global');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container-global';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        // z-index garante que o toast fique acima de modais e outros elementos
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    return container;
}

/**
 * Exibe um toast de notificação Bootstrap 5 no canto inferior direito da tela.
 *
 * @param {string} mensagem - Mensagem a ser exibida.
 * @param {string} tipo     - 'success' | 'danger' | 'warning' | 'info'
 * @param {number} duracao  - Tempo em ms antes de fechar automaticamente. (padrão: 4000)
 */
function showToast(mensagem, tipo = 'info', duracao = 4000) {
    const container = getToastContainer();

    // Mapeia o tipo para o ícone Bootstrap Icons correspondente
    const icones = {
        success: 'bi-check-circle-fill',
        danger:  'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill',
    };
    const icone = icones[tipo] ?? 'bi-bell-fill';

    // Cria o elemento do toast dinamicamente
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-bg-${tipo} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi ${icone}"></i>
                <span>${mensagem}</span>
            </div>
            <button type="button"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"
                    aria-label="Fechar">
            </button>
        </div>`;

    container.appendChild(toastEl);

    // Instancia e exibe via API do Bootstrap 5
    const toast = new bootstrap.Toast(toastEl, { delay: duracao });
    toast.show();

    // Remove o elemento do DOM após o fechamento para não acumular elementos ocultos
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}
