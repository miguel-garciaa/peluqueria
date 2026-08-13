import { RefreshCw, WifiOff, X } from "lucide-react";
import { useEffect, useState } from "react";
import { useOnlineStatus } from "@/hooks/use-online-status";
import { applyPwaUpdate, PWA_UPDATE_AVAILABLE_EVENT } from "@/lib/pwa";

export function PwaStatus() {
  const isOnline = useOnlineStatus();
  const [updateAvailable, setUpdateAvailable] = useState(false);

  useEffect(() => {
    const showUpdate = () => setUpdateAvailable(true);
    window.addEventListener(PWA_UPDATE_AVAILABLE_EVENT, showUpdate);
    return () => window.removeEventListener(PWA_UPDATE_AVAILABLE_EVENT, showUpdate);
  }, []);

  if (isOnline && !updateAvailable) return null;

  return (
    <aside
      className="pwa-status fixed inset-x-3 bottom-3 z-[80] mx-auto max-w-md rounded-xl bg-ink px-4 py-3 text-white shadow-lg"
      aria-live="polite"
      role={isOnline ? "status" : "alert"}
    >
      {!isOnline ? (
        <div className="flex items-center gap-3">
          <WifiOff className="size-5 shrink-0 text-brass" aria-hidden="true" />
          <div><p className="text-sm font-bold">Sin conexión</p><p className="text-xs leading-5 text-white/65">Reservar, consultar disponibilidad y cancelar requieren internet.</p></div>
        </div>
      ) : (
        <div className="flex items-center gap-3">
          <RefreshCw className="size-5 shrink-0 text-brass" aria-hidden="true" />
          <div className="min-w-0 flex-1"><p className="text-sm font-bold">Hay una nueva versión disponible</p><div className="mt-2 flex gap-2"><button type="button" onClick={() => void applyPwaUpdate()} className="min-h-10 rounded-full bg-brass px-4 text-xs font-bold text-ink">Actualizar ahora</button><button type="button" onClick={() => setUpdateAvailable(false)} className="min-h-10 rounded-full px-3 text-xs font-bold text-white/70 hover:bg-white/10">Más tarde</button></div></div>
          <button type="button" onClick={() => setUpdateAvailable(false)} className="grid size-10 shrink-0 place-items-center rounded-full text-white/60 hover:bg-white/10 hover:text-white" aria-label="Cerrar aviso de actualización"><X className="size-4" aria-hidden="true" /></button>
        </div>
      )}
    </aside>
  );
}
