import { BellOff, BellRing, LoaderCircle } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import {
  disablePushNotifications,
  enablePushNotifications,
  inspectPushNotifications,
  type PushNotificationConfig,
  type PushNotificationState,
} from "@/lib/push-notifications";
import { cn } from "@/lib/utils";

interface PushNotificationSettingsProps extends PushNotificationConfig {
  tone?: "dark" | "light";
}

const descriptions: Record<PushNotificationState, string> = {
  checking: "Comprobando la configuración de este dispositivo…",
  loading: "Guardando tus preferencias…",
  idle: "Recibe la confirmación, los cambios y un recordatorio antes de cada cita.",
  enabled: "Te avisaremos en este dispositivo aunque la app esté cerrada.",
  denied: "Las notificaciones están bloqueadas. Puedes permitirlas desde los ajustes del navegador.",
  unavailable: "Instala la app y ábrela desde la pantalla de inicio para recibir avisos en este dispositivo.",
  error: "No hemos podido cambiar la configuración. Comprueba la conexión e inténtalo de nuevo.",
};

export function PushNotificationSettings({ publicKey, subscribeEndpoint, csrfToken, tone = "dark" }: PushNotificationSettingsProps) {
  const config = useMemo(() => ({ publicKey, subscribeEndpoint, csrfToken }), [publicKey, subscribeEndpoint, csrfToken]);
  const [state, setState] = useState<PushNotificationState>("checking");
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;
    void inspectPushNotifications(config)
      .then((nextState) => { if (active) setState(nextState); })
      .catch(() => { if (active) setState("error"); });
    return () => { active = false; };
  }, [config]);

  const toggle = async () => {
    setError(null);
    setState("loading");
    try {
      if (Notification.permission === "granted") {
        const registration = await navigator.serviceWorker.getRegistration("/");
        const subscription = await registration?.pushManager.getSubscription();
        if (subscription) {
          await disablePushNotifications(config);
          setState("idle");
          return;
        }
      }

      await enablePushNotifications(config);
      setState("enabled");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : null);
      setState(Notification.permission === "denied" ? "denied" : "error");
    }
  };

  const dark = tone === "dark";
  const busy = state === "checking" || state === "loading";
  const enabled = state === "enabled";
  const actionable = !busy && state !== "unavailable" && state !== "denied";

  return (
    <section className={cn("rounded-2xl border p-5", dark ? "border-white/12 bg-white/[0.045]" : "border-ink/10 bg-white")} aria-labelledby="push-settings-title">
      <div className="flex items-start gap-4">
        <span className={cn("grid size-12 shrink-0 place-items-center rounded-full", enabled ? "bg-brass text-ink" : dark ? "bg-white/10 text-brass" : "bg-mist text-brass-deep")}>
          {busy ? <LoaderCircle className="size-5 animate-spin" aria-hidden="true" /> : enabled ? <BellRing className="size-5" aria-hidden="true" /> : <BellOff className="size-5" aria-hidden="true" />}
        </span>
        <div className="min-w-0 flex-1">
          <h2 id="push-settings-title" className="font-display text-xl font-semibold">Avisos de tus citas</h2>
          <p className={cn("mt-1 text-sm leading-6", dark ? "text-white/60" : "text-taupe")}>{error ?? descriptions[state]}</p>
          {actionable && (
            <button type="button" onClick={() => void toggle()} className={cn("mt-4 min-h-11 rounded-full px-5 text-sm font-bold transition-colors", enabled ? dark ? "border border-white/15 text-white hover:bg-white/10" : "border border-ink/15 text-ink hover:bg-ink/5" : "bg-brass text-ink hover:bg-brass-light")}>
              {enabled ? "Desactivar en este dispositivo" : "Activar avisos"}
            </button>
          )}
        </div>
      </div>
    </section>
  );
}
