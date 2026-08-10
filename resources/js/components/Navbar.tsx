import { ArrowUpRight, LogOut, Menu, UserRound, X } from "lucide-react";
import { type MouseEvent, useEffect, useState } from "react";
import { BrandMark } from "@/components/BrandMark";
import type { CurrentUser } from "@/App";
import { cn } from "@/lib/utils";

const links = [
  ["Inicio", "#inicio", "01"], ["Servicios", "#servicios", "02"], ["Equipo", "#equipo", "03"], ["Galería", "#galeria", "04"], ["Reservas", "#reservas", "05"],
] as const;

type NavbarProps = { currentUser?: CurrentUser | null; csrfToken?: string };

function AccountControl({ currentUser, csrfToken, mobile = false }: NavbarProps & { mobile?: boolean }) {
  if (!currentUser) {
    return <a href="/auth/google" aria-label="Iniciar sesión con Google" className={cn("group flex min-h-11 items-center justify-center gap-2 rounded-full border border-white/20 px-4 text-sm font-bold text-white transition-colors hover:border-brass hover:bg-white/10", mobile && "mt-4 w-full")}><span aria-hidden="true" className="grid size-7 place-items-center rounded-full bg-white/10 text-brass transition-colors group-hover:bg-brass group-hover:text-ink"><UserRound className="size-4" /></span><span>Cuenta</span></a>;
  }

  if (mobile) {
    return <div className="mt-4 rounded-2xl border border-white/15 p-4"><div className="flex items-center gap-3">{currentUser.avatarUrl ? <img src={currentUser.avatarUrl} alt="" referrerPolicy="no-referrer" className="size-10 rounded-full object-cover" /> : <span className="grid size-10 place-items-center rounded-full bg-white/10"><UserRound className="size-5" /></span>}<div className="min-w-0"><p className="truncate font-bold">{currentUser.name}</p><p className="truncate text-xs text-white/55">{currentUser.email}</p></div></div><form action="/logout" method="post" className="mt-3"><input type="hidden" name="_token" value={csrfToken} /><button type="submit" className="flex min-h-11 w-full items-center justify-center gap-2 rounded-full border border-white/15 text-sm font-bold"><LogOut className="size-4" />Cerrar sesión</button></form></div>;
  }

  return <details className="group/account relative"><summary aria-label="Abrir cuenta" className="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-full border border-white/20 px-2.5 pr-4 text-sm font-bold text-white transition-colors hover:border-brass [&::-webkit-details-marker]:hidden">{currentUser.avatarUrl ? <img src={currentUser.avatarUrl} alt="" referrerPolicy="no-referrer" className="size-7 rounded-full object-cover" /> : <span className="grid size-7 place-items-center rounded-full bg-white/10"><UserRound className="size-4" /></span>}<span className="max-w-24 truncate">{currentUser.name.split(" ")[0]}</span></summary><div className="absolute right-0 top-[calc(100%+0.75rem)] w-64 rounded-2xl border border-white/10 bg-ink p-4 shadow-2xl"><p className="truncate font-bold">{currentUser.name}</p><p className="mt-1 truncate text-xs text-white/55">{currentUser.email}</p><form action="/logout" method="post" className="mt-3 border-t border-white/10 pt-3"><input type="hidden" name="_token" value={csrfToken} /><button type="submit" className="flex min-h-10 w-full items-center justify-center gap-2 rounded-full bg-white/10 text-sm font-bold transition-colors hover:bg-white/15"><LogOut className="size-4" />Cerrar sesión</button></form></div></details>;
}

export function Navbar({ currentUser = null, csrfToken = "" }: NavbarProps) {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const [activeHref, setActiveHref] = useState("#inicio");

  useEffect(() => {
    const onScroll = () => {
      setIsScrolled(window.scrollY > 24);
      const marker = window.scrollY + window.innerHeight * 0.35;
      const active = links.reduce((current, [, href]) => {
        const section = document.querySelector<HTMLElement>(href);
        return section && section.offsetTop <= marker ? href : current;
      }, "#inicio");
      setActiveHref(active);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = isMenuOpen ? "hidden" : "";
    const onKey = (event: KeyboardEvent) => event.key === "Escape" && setIsMenuOpen(false);
    document.addEventListener("keydown", onKey);
    return () => { document.body.style.overflow = ""; document.removeEventListener("keydown", onKey); };
  }, [isMenuOpen]);

  const navigateToSection = (event: MouseEvent<HTMLAnchorElement>, href: string) => {
    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const target = document.querySelector<HTMLElement>(href);
    if (!target) return;

    event.preventDefault();
    setIsMenuOpen(false);
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    target.scrollIntoView({ behavior: reduceMotion ? "auto" : "smooth", block: "start" });
    window.history.pushState(null, "", href);
  };

  return (
    <header className={cn("fixed inset-x-0 top-0 z-50 text-white transition-all duration-300", isScrolled || isMenuOpen ? "bg-ink/95 shadow-[0_1px_0_oklch(1_0_0/0.12)] backdrop-blur-md" : "bg-gradient-to-b from-black/45 to-transparent")}>
      <span aria-hidden="true" className="navbar-accent-line" />
      <span aria-hidden="true" className="scroll-progress" />
      <nav className="navbar-shell grid h-20 grid-cols-[1fr_auto] items-center lg:h-24 xl:grid-cols-[minmax(13rem,1fr)_auto_minmax(13rem,1fr)]" aria-label="Navegación principal">
        <a href="#inicio" onClick={(event) => navigateToSection(event, "#inicio")} className="flex min-h-11 items-center gap-3 font-display text-[1.05rem] font-extrabold tracking-[-0.035em] sm:text-xl lg:text-2xl" aria-label="Baskuñana Peluqueros, inicio">
          <BrandMark className="size-8 sm:size-9 lg:size-10" />
          <span className="leading-none">BASKUÑANA<span className="ml-1 text-brass">*</span><span className="mt-1 block text-[0.62em] font-normal tracking-[0.08em] text-white/60">Peluqueros · Cartagena</span></span>
        </a>
        <div className="hidden items-center justify-center gap-7 xl:flex">
          {links.map(([label, href, number]) => <a key={href} href={href} aria-label={`${label} ${number}`} onClick={(event) => navigateToSection(event, href)} aria-current={activeHref === href ? "page" : undefined} className={cn("nav-link relative py-4 text-[0.95rem] font-bold transition-colors", activeHref === href ? "text-white" : "text-white/55 hover:text-white")}><span>{label}</span><sup>{number}</sup></a>)}
        </div>
        <div className="hidden items-center justify-self-end gap-3 xl:flex">
          <AccountControl currentUser={currentUser} csrfToken={csrfToken} />
          <a href="#reservas" onClick={(event) => navigateToSection(event, "#reservas")} className="navbar-cta group flex items-center gap-3 rounded-full bg-white p-2 pr-5 text-[0.95rem] font-bold transition-transform hover:-translate-y-0.5"><span className="grid size-9 place-items-center rounded-full bg-brass-deep text-white"><ArrowUpRight className="size-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" /></span><span className="whitespace-nowrap text-ink">Reservar cita</span></a>
        </div>
        <button type="button" onClick={() => setIsMenuOpen((v) => !v)} className="grid size-11 place-items-center rounded-full border border-current/20 xl:hidden" aria-expanded={isMenuOpen} aria-controls="mobile-menu" aria-label={isMenuOpen ? "Cerrar menú" : "Abrir menú"}>
          {isMenuOpen ? <X aria-hidden="true" /> : <Menu aria-hidden="true" />}
        </button>
      </nav>
      <div id="mobile-menu" className={cn("grid overflow-hidden bg-ink text-white transition-[grid-template-rows] duration-300 xl:hidden", isMenuOpen ? "grid-rows-[1fr]" : "grid-rows-[0fr]")}>
        <div className="min-h-0"><div className="navbar-shell flex flex-col gap-1 pb-6 pt-2">
          {links.map(([label, href, number]) => <a key={href} href={href} aria-label={`${label} ${number}`} onClick={(event) => navigateToSection(event, href)} className="flex items-start justify-between border-b border-white/10 py-4 font-display text-2xl font-semibold"><span>{label}</span><sup className="font-sans text-xs text-brass">{number}</sup></a>)}
          <AccountControl currentUser={currentUser} csrfToken={csrfToken} mobile />
          <a href="#reservas" onClick={(event) => navigateToSection(event, "#reservas")} className="navbar-cta mt-4 flex items-center justify-center gap-3 rounded-full bg-white p-2 pr-5 font-bold"><span className="grid size-10 place-items-center rounded-full bg-brass-deep text-white"><ArrowUpRight className="size-4" /></span><span className="whitespace-nowrap text-ink">Reservar cita</span></a>
        </div></div>
      </div>
    </header>
  );
}
