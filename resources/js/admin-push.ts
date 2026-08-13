import {
  disablePushNotifications,
  enablePushNotifications,
  inspectPushNotifications,
  type PushNotificationConfig,
  type PushNotificationState,
} from "@/lib/push-notifications";

const copy: Record<PushNotificationState, { description: string; button: string }> = {
  checking: { description: "Comprobando este dispositivo…", button: "Comprobando…" },
  loading: { description: "Guardando la configuración…", button: "Guardando…" },
  idle: { description: "Activa los avisos para recibir cada nueva reserva y cancelación.", button: "Activar avisos" },
  enabled: { description: "Este dispositivo recibirá las nuevas reservas y cancelaciones.", button: "Desactivar avisos" },
  denied: { description: "Los avisos están bloqueados en los ajustes del navegador.", button: "Avisos bloqueados" },
  unavailable: { description: "Este navegador no admite Web Push o faltan las claves VAPID.", button: "No disponible" },
  error: { description: "No se pudo cambiar la configuración. Comprueba la conexión.", button: "Reintentar" },
};

function initPushSettings(root: HTMLElement) {
  if (root.dataset.initialized === "true") return;
  root.dataset.initialized = "true";

  const button = root.querySelector<HTMLButtonElement>("[data-push-toggle]");
  const description = root.querySelector<HTMLElement>("[data-push-description]");
  if (!button || !description) return;

  const config: PushNotificationConfig = {
    publicKey: root.dataset.publicKey ?? "",
    subscribeEndpoint: root.dataset.subscriptionEndpoint ?? "/notificaciones/suscripcion",
    csrfToken: root.dataset.csrfToken ?? "",
  };
  let current: PushNotificationState = "checking";

  const render = (state: PushNotificationState, error?: string) => {
    current = state;
    description.textContent = error ?? copy[state].description;
    button.textContent = copy[state].button;
    button.disabled = ["checking", "loading", "denied", "unavailable"].includes(state);
  };

  button.addEventListener("click", async () => {
    const disabling = current === "enabled";
    render("loading");
    try {
      if (disabling) {
        await disablePushNotifications(config);
        render("idle");
      } else {
        await enablePushNotifications(config);
        render("enabled");
      }
    } catch (caught) {
      render(Notification.permission === "denied" ? "denied" : "error", caught instanceof Error ? caught.message : undefined);
    }
  });

  void inspectPushNotifications(config).then(render).catch(() => render("error"));
}

function initAll() {
  document.querySelectorAll<HTMLElement>("[data-admin-push-settings]").forEach(initPushSettings);
}

initAll();
document.addEventListener("livewire:navigated", initAll);
