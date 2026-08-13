import { CalendarDays, CalendarPlus, Home, Images, LayoutDashboard, LogOut, Scissors, UserRound, X } from "lucide-react";
import { type MouseEvent, useEffect, useRef } from "react";
import { InstallAppButton } from "@/components/InstallAppButton";
import { cn } from "@/lib/utils";
import type { CurrentUser } from "@/types";

type MobileBottomNavProps = {
  activeHref: string;
  currentUser: CurrentUser | null;
  csrfToken: string;
  onBook?: () => void;
  onNavigate: (event: MouseEvent<HTMLAnchorElement>, href: string) => void;
  solid: boolean;
};

const itemClass = "mobile-bottom-nav__item";

export function MobileBottomNav({ activeHref, currentUser, csrfToken, onBook, onNavigate, solid }: MobileBottomNavProps) {
  const accountDialogRef = useRef<HTMLDialogElement>(null);
  const accountButtonRef = useRef<HTMLButtonElement>(null);
  const isAdmin = currentUser?.isAdmin === true;

  useEffect(() => {
    const dialog = accountDialogRef.current;
    const restoreFocus = () => accountButtonRef.current?.focus();
    dialog?.addEventListener("close", restoreFocus);
    return () => dialog?.removeEventListener("close", restoreFocus);
  }, []);

  const sectionHref = (href: string) => solid ? `/${href}` : href;
  const sectionClick = (href: string) => solid ? undefined : (event: MouseEvent<HTMLAnchorElement>) => onNavigate(event, href);

  return (
    <>
      <nav className="mobile-bottom-nav md:hidden" aria-label="Navegación móvil">
        <div className="mobile-bottom-nav__items">
          <a href={sectionHref("#inicio")} onClick={sectionClick("#inicio")} className={cn(itemClass, !solid && activeHref === "#inicio" && "is-active")} aria-current={!solid && activeHref === "#inicio" ? "page" : undefined}>
            <Home aria-hidden="true" />
            <span>Inicio</span>
          </a>
          <a href={sectionHref("#servicios")} onClick={sectionClick("#servicios")} className={cn(itemClass, !solid && activeHref === "#servicios" && "is-active")} aria-current={!solid && activeHref === "#servicios" ? "page" : undefined}>
            <Scissors aria-hidden="true" />
            <span>Servicios</span>
          </a>
          {isAdmin ? (
            <a href="/admin" className={`${itemClass} mobile-bottom-nav__primary`}>
              <span className="mobile-bottom-nav__primary-icon"><LayoutDashboard aria-hidden="true" /></span>
              <span>Panel</span>
            </a>
          ) : onBook ? (
            <button type="button" onClick={onBook} className={`${itemClass} mobile-bottom-nav__primary`}>
              <span className="mobile-bottom-nav__primary-icon"><CalendarPlus aria-hidden="true" /></span>
              <span>Reservar</span>
            </button>
          ) : (
            <a href={sectionHref("#reservas")} onClick={sectionClick("#reservas")} className={cn(itemClass, "mobile-bottom-nav__primary", !solid && activeHref === "#reservas" && "is-active")} aria-current={!solid && activeHref === "#reservas" ? "page" : undefined}>
              <span className="mobile-bottom-nav__primary-icon"><CalendarPlus aria-hidden="true" /></span>
              <span>Reservar</span>
            </a>
          )}
          <a href={sectionHref("#galeria")} onClick={sectionClick("#galeria")} className={cn(itemClass, !solid && activeHref === "#galeria" && "is-active")} aria-current={!solid && activeHref === "#galeria" ? "page" : undefined}>
            <Images aria-hidden="true" />
            <span>Galería</span>
          </a>
          <button ref={accountButtonRef} type="button" onClick={() => accountDialogRef.current?.showModal()} className={cn(itemClass, solid && "is-active")} aria-haspopup="dialog">
            <UserRound aria-hidden="true" />
            <span>Cuenta</span>
          </button>
        </div>
      </nav>

      <dialog
        ref={accountDialogRef}
        className="mobile-account-dialog md:hidden"
        aria-labelledby="mobile-account-title"
        onClick={(event) => event.target === event.currentTarget && event.currentTarget.close()}
      >
        <div className="mobile-account-dialog__content">
          <div className="flex items-start justify-between gap-4">
            <div className="flex min-w-0 items-center gap-3">
              {currentUser?.avatarUrl ? <img src={currentUser.avatarUrl} alt="" referrerPolicy="no-referrer" className="size-12 rounded-full object-cover" /> : <span className="grid size-12 shrink-0 place-items-center rounded-full bg-white/10 text-brass"><UserRound className="size-5" aria-hidden="true" /></span>}
              <div className="min-w-0">
                <h2 id="mobile-account-title" className="truncate font-display text-xl font-semibold">{currentUser ? currentUser.name : "Tu cuenta"}</h2>
                <p className="mt-0.5 truncate text-sm text-white/55">{currentUser ? currentUser.email : "Gestiona tus citas desde cualquier lugar"}</p>
              </div>
            </div>
            <button type="button" onClick={() => accountDialogRef.current?.close()} className="grid size-11 shrink-0 place-items-center rounded-full text-white/60 hover:bg-white/10 hover:text-white" aria-label="Cerrar cuenta"><X className="size-5" aria-hidden="true" /></button>
          </div>

          {currentUser ? (
            <div className="mt-5 grid gap-2">
              {isAdmin ? <a href="/admin" className="mobile-account-action"><LayoutDashboard aria-hidden="true" />Panel de control</a> : <a href="/mis-citas" className="mobile-account-action"><CalendarDays aria-hidden="true" />Mis citas</a>}
              <form action="/logout" method="post">
                <input type="hidden" name="_token" value={csrfToken} />
                <button type="submit" className="mobile-account-action w-full"><LogOut aria-hidden="true" />Cerrar sesión</button>
              </form>
            </div>
          ) : (
            <a href="/auth/google" className="mobile-account-action mt-5"><UserRound aria-hidden="true" />Iniciar sesión o registrarme</a>
          )}
          <InstallAppButton />
        </div>
      </dialog>
    </>
  );
}
