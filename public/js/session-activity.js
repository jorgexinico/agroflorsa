(() => {
    const script = document.currentScript;
    if (!script) return;
    const endpoint = script.dataset.activityUrl;
    const token = script.dataset.activityToken;
    let last = Date.now(), busy = false;
    // Solo actividad humana: una pestaña abierta sin interacción no renueva.
    async function activity(event) {
        if (!event.isTrusted || document.hidden || busy || Date.now() - last < 60000) return;
        busy = true; last = Date.now();
        try {
            const response = await fetch(endpoint, {method: 'POST', credentials: 'same-origin', headers: {'X-Activity-Token': token}});
            if (!response.ok) {
                let notice = document.getElementById('session-activity-notice');
                if (!notice) {
                    notice = document.createElement('div'); notice.id = 'session-activity-notice';
                    notice.setAttribute('role', 'alert'); notice.className = 'alert alert-warning';
                    document.querySelector('main')?.prepend(notice);
                }
                notice.textContent = response.status === 401 ? 'Tu sesión venció. Conserva lo escrito y vuelve a iniciar sesión en otra pestaña antes de guardar.' : 'No se pudo mantener la conexión con Login. Conserva lo escrito mientras se recupera.';
            } else document.getElementById('session-activity-notice')?.remove();
        } catch (_) { /* Reintentar con la siguiente interacción; no navegar ni borrar formularios. */ }
        finally { busy = false; }
    }
    for (const name of ['pointerdown', 'keydown', 'input', 'scroll']) document.addEventListener(name, activity, {passive: true, capture: true});
})();
