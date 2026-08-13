import { CalendarDays, LayoutDashboard, LogIn, LogOut, UserRound } from "lucide-react";
import { InstallAppButton } from "@/components/InstallAppButton";
import { PushNotificationSettings } from "@/components/PushNotificationSettings";
import type { CurrentUser } from "@/types";

interface MobileAccountPageProps {
  currentUser: CurrentUser | null;
  csrfToken: string;
  pushPublicKey?: string;
  pushSubscriptionEndpoint?: string;
}

export function MobileAccountPage({ currentUser, csrfToken, pushPublicKey = "", pushSubscriptionEndpoint = "/notificaciones/suscripcion" }: MobileAccountPageProps) {
  const isAdmin = currentUser?.isAdmin === true;

  return (
    <section id="cuenta" className="mobile-account-page min-h-[calc(100svh-4.75rem)] bg-ink text-white" aria-labelledby="mobile-account-title">
      <div className="container-shell pb-12 pt-28">
        <p className="font-semibold text-brass">Tu espacio personal</p>
        <h1 id="mobile-account-title" className="mt-2 font-display text-5xl font-semibold tracking-[-0.035em]">Cuenta</h1>

        {currentUser ? (
          <div className="mt-8">
            <div className="flex items-center gap-4 border-b border-white/12 pb-6">
              {currentUser.avatarUrl ? (
                <img src={currentUser.avatarUrl} alt="" referrerPolicy="no-referrer" className="size-16 rounded-full object-cover" />
              ) : (
                <span className="grid size-16 shrink-0 place-items-center rounded-full bg-white/10 text-brass"><UserRound className="size-7" aria-hidden="true" /></span>
              )}
              <div className="min-w-0">
                <h2 className="truncate font-display text-2xl font-semibold">{currentUser.name}</h2>
                <p className="mt-1 truncate text-sm text-white/60">{currentUser.email}</p>
              </div>
            </div>
            <div className="mt-5 grid gap-3">
              {isAdmin ? (
                <a href="/admin" className="mobile-account-action"><LayoutDashboard aria-hidden="true" />Panel de control</a>
              ) : (
                <a href="/mis-citas" className="mobile-account-action"><CalendarDays aria-hidden="true" />Mis citas</a>
              )}
              <form action="/logout" method="post">
                <input type="hidden" name="_token" value={csrfToken} />
                <button type="submit" className="mobile-account-action w-full"><LogOut aria-hidden="true" />Cerrar sesión</button>
              </form>
            </div>
            <div className="mt-5">
              <PushNotificationSettings publicKey={pushPublicKey} subscribeEndpoint={pushSubscriptionEndpoint} csrfToken={csrfToken} audience={isAdmin ? "admin" : "customer"} />
            </div>
          </div>
        ) : (
          <div className="mt-8 border-y border-white/12 py-7">
            <span className="grid size-14 place-items-center rounded-full bg-white/10 text-brass"><UserRound className="size-6" aria-hidden="true" /></span>
            <h2 className="mt-5 font-display text-2xl font-semibold">Tus citas, siempre a mano</h2>
            <p className="mt-2 max-w-md text-sm leading-6 text-white/65">Inicia sesión para reservar, consultar tus próximas citas y recibir las confirmaciones.</p>
            <a href="/auth/google" className="mobile-account-action mt-5 bg-white text-ink hover:bg-brass"><LogIn aria-hidden="true" />Iniciar sesión con Google</a>
          </div>
        )}

        <InstallAppButton />
      </div>
    </section>
  );
}
