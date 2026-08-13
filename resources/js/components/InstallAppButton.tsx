import { Download, Share2, X } from "lucide-react";
import { useEffect, useState } from "react";
import { isStandaloneDisplayMode } from "@/lib/display-mode";

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed"; platform: string }>;
}

function isIosDevice() {
  const appleMobile = /iPad|iPhone|iPod/.test(navigator.userAgent);
  const modernIpad = navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1;
  return appleMobile || modernIpad;
}

export function InstallAppButton() {
  const [installPrompt, setInstallPrompt] = useState<BeforeInstallPromptEvent | null>(null);
  const [isStandalone, setIsStandalone] = useState(isStandaloneDisplayMode);
  const [showInstallHelp, setShowInstallHelp] = useState(false);
  const isIos = isIosDevice();

  useEffect(() => {
    const displayMode = window.matchMedia("(display-mode: standalone)");
    const onDisplayModeChange = () => setIsStandalone(isStandaloneDisplayMode());
    const onBeforeInstallPrompt = (event: Event) => {
      event.preventDefault();
      setInstallPrompt(event as BeforeInstallPromptEvent);
    };
    const onInstalled = () => {
      setInstallPrompt(null);
      setShowInstallHelp(false);
      setIsStandalone(true);
    };

    window.addEventListener("beforeinstallprompt", onBeforeInstallPrompt);
    window.addEventListener("appinstalled", onInstalled);
    displayMode.addEventListener("change", onDisplayModeChange);
    return () => {
      window.removeEventListener("beforeinstallprompt", onBeforeInstallPrompt);
      window.removeEventListener("appinstalled", onInstalled);
      displayMode.removeEventListener("change", onDisplayModeChange);
    };
  }, []);

  if (isStandalone) return null;

  const install = async () => {
    if (isIos) {
      setShowInstallHelp(true);
      return;
    }
    if (!installPrompt) {
      setShowInstallHelp(true);
      return;
    }

    await installPrompt.prompt();
    const choice = await installPrompt.userChoice;
    setInstallPrompt(null);
    if (choice.outcome === "accepted") setIsStandalone(true);
  };

  return (
    <div className="mt-4 border-t border-white/10 pt-4 md:hidden">
      <button
        type="button"
        onClick={install}
        className="flex min-h-12 w-full items-center justify-center gap-3 rounded-full bg-brass px-5 text-sm font-extrabold text-ink transition-colors hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
      >
        <Download className="size-5" aria-hidden="true" />
        Instalar app
      </button>
      {showInstallHelp && (
        <div className="relative mt-3 rounded-xl bg-white/8 p-4 pr-12 text-sm leading-6 text-white" role="status">
          <button
            type="button"
            onClick={() => setShowInstallHelp(false)}
            className="absolute right-2 top-2 grid size-10 place-items-center rounded-full text-white/65 hover:bg-white/10 hover:text-white"
            aria-label="Cerrar instrucciones de instalación"
          >
            <X className="size-4" aria-hidden="true" />
          </button>
          <p className="flex items-center gap-2 font-bold"><Share2 className="size-4 text-brass" aria-hidden="true" />{isIos ? "Instalar en iPhone" : "Instalar desde el navegador"}</p>
          <p className="mt-1 text-white/70">{isIos ? "Pulsa Compartir y después “Añadir a pantalla de inicio”." : "Abre el menú del navegador y pulsa “Instalar aplicación” o “Añadir a pantalla de inicio”."}</p>
        </div>
      )}
    </div>
  );
}
