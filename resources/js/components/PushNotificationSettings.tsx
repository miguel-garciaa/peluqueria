import { BellOff, BellRing, LoaderCircle } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import {
  disablePushNotifications,
  enablePushNotifications,
  inspectPushNotifications,
  sendTestPushNotification,
  type PushNotificationConfig,
  type PushNotificationState,
} from "@/lib/push-notifications";
import { cn } from "@/lib/utils";

interface PushNotificationSettingsProps extends PushNotificationConfig {
  tone?: "dark" | "light";
  audience?: "customer" | "admin";
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

const adminDescriptions: Record<PushNotificationState, string> = {
  ...descriptions,
  idle: "Activa los avisos para recibir cada nueva reserva y cancelación en este dispositivo.",
  enabled: "Este dispositivo está preparado para recibir las nuevas reservas aunque la app esté cerrada.",
};

export function PushNotificationSettings({ publicKey, subscribeEndpoint, csrfToken, tone = "dark", audience = "customer" }: PushNotificationSettingsProps) {
  const config = useMemo(() => ({ publicKey, subscribeEndpoint, csrfToken }), [publicKey, subscribeEndpoint, csrfToken]);
  const [state, setState] = useState<PushNotificationState>("checking");
  const [error, setError] = useState<string | null>(null);
  const [testStatus, setTestStatus] = useState<"idle" | "sending" | "sent" | "error">("idle");
  const [testMessage, setTestMessage] = useState<string | null>(null);

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
      if (state === "enabled") {
        await disablePushNotifications(config);
        setState("idle");
        setTestStatus("idle");
        setTestMessage(null);
        return;
      }

      await enablePushNotifications(config);
      setState("enabled");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : null);
      setState(Notification.permission === "denied" ? "denied" : "error");
    }
  };

  const sendTest = async () => {
    setTestStatus("sending");
    setTestMessage(null);
    try {
      const message = await sendTestPushNotification(config);
      setTestMessage(message);
      setTestStatus("sent");
    } catch (caught) {
      setTestMessage(caught instanceof Error ? caught.message : "No se pudo enviar la prueba.");
      setTestStatus("error");
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
          <h2 id="push-settings-title" className="font-display text-xl font-semibold">{audience === "admin" ? "Avisos de nuevas reservas" : "Avisos de tus citas"}</h2>
          <p className={cn("mt-1 text-sm leading-6", dark ? "text-white/60" : "text-taupe")}>{error ?? (audience === "admin" ? adminDescriptions[state] : descriptions[state])}</p>
          {actionable && (
            <div className="mt-4 flex flex-wrap items-center gap-2">
              {enabled && (
                <button type="button" onClick={() => void sendTest()} disabled={testStatus === "sending"} className="min-h-11 rounded-full bg-brass px-5 text-sm font-bold text-ink transition-colors hover:bg-brass-light disabled:cursor-wait disabled:opacity-60">
                  {testStatus === "sending" ? "Enviando prueba…" : "Enviar prueba al móvil"}
                </button>
              )}
              <button type="button" onClick={() => void toggle()} className={cn("min-h-11 rounded-full px-5 text-sm font-bold transition-colors", enabled ? dark ? "border border-white/15 text-white hover:bg-white/10" : "border border-ink/15 text-ink hover:bg-ink/5" : "bg-brass text-ink hover:bg-brass-light")}>
                {enabled ? "Desactivar" : "Activar avisos"}
              </button>
            </div>
          )}
          {testMessage && <p role={testStatus === "error" ? "alert" : "status"} className={cn("mt-3 text-sm font-semibold", testStatus === "error" ? "text-danger" : dark ? "text-brass-light" : "text-brass-deep")}>{testMessage}</p>}
        </div>
      </div>
    </section>
  );
}
