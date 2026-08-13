import { CalendarPlus, Home, Images, LayoutDashboard, Scissors, UserRound, UsersRound } from "lucide-react";
import { type MouseEvent } from "react";
import { mobileViewPaths } from "@/lib/mobile-navigation";
import { cn } from "@/lib/utils";
import type { CurrentUser, MobileView } from "@/types";

type MobileBottomNavProps = {
  activeView: MobileView;
  currentUser: CurrentUser | null;
  onViewChange?: (view: MobileView) => void;
};

const itemClass = "mobile-bottom-nav__item";

export function MobileBottomNav({ activeView, currentUser, onViewChange }: MobileBottomNavProps) {
  const isAdmin = currentUser?.isAdmin === true;

  const handleViewClick = (event: MouseEvent<HTMLAnchorElement>, view: MobileView) => {
    if (!onViewChange || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    onViewChange(view);
  };

  const viewItem = (view: MobileView, label: string, Icon: typeof Home) => (
    <a
      href={mobileViewPaths[view]}
      onClick={(event) => handleViewClick(event, view)}
      className={cn(itemClass, activeView === view && "is-active")}
      aria-current={activeView === view ? "page" : undefined}
    >
      <Icon aria-hidden="true" />
      <span>{label}</span>
    </a>
  );

  return (
    <nav className="mobile-bottom-nav md:hidden" aria-label="Navegación móvil">
      <div className="mobile-bottom-nav__items">
        {viewItem("inicio", "Inicio", Home)}
        {viewItem("servicios", "Servicios", Scissors)}
        {viewItem("equipo", "Equipo", UsersRound)}
        {isAdmin ? (
          <a href="/admin" className={`${itemClass} mobile-bottom-nav__primary`}>
            <span className="mobile-bottom-nav__primary-icon"><LayoutDashboard aria-hidden="true" /></span>
            <span>Panel</span>
          </a>
        ) : (
          <a
            href={mobileViewPaths.reservar}
            onClick={(event) => handleViewClick(event, "reservar")}
            className={cn(itemClass, "mobile-bottom-nav__primary", activeView === "reservar" && "is-active")}
            aria-current={activeView === "reservar" ? "page" : undefined}
          >
            <span className="mobile-bottom-nav__primary-icon"><CalendarPlus aria-hidden="true" /></span>
            <span>Reservar</span>
          </a>
        )}
        {viewItem("galeria", "Galería", Images)}
        {viewItem("cuenta", "Cuenta", UserRound)}
      </div>
    </nav>
  );
}
