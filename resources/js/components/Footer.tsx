import { Clock3, Facebook, Instagram, MapPin, MessageCircle, Music2, Phone, X } from "lucide-react";
import { BrandMark } from "@/components/BrandMark";
import { site } from "@/data/site";

const socialLinks = [
  { label: "Instagram", href: site.social.instagram, icon: Instagram },
  { label: "X", href: site.social.x, icon: X },
  { label: "Facebook", href: site.social.facebook, icon: Facebook },
  { label: "TikTok", href: site.social.tiktok, icon: Music2 },
  { label: "WhatsApp", href: site.social.whatsapp, icon: MessageCircle },
] as const;

export function Footer() {
  return (
    <footer className="bg-ink pb-8 text-white">
      <div className="container-shell border-t border-white/10 pt-10">
        <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <a href="#inicio" className="flex items-center gap-3 font-display text-xl font-semibold">
              <BrandMark className="size-8 text-brass" />
              <span className="uppercase">{site.name}</span> <span className="font-normal text-white/55">&amp; Barbería</span>
            </a>
            <p className="mt-4 max-w-xs text-sm leading-6 text-white/45">Corte, color, barbería y cuidado capilar con atención personalizada en tu ciudad.</p>
            <nav className="mt-6 flex flex-wrap gap-2" aria-label="Redes sociales">
              {socialLinks.map(({ label, href, icon: Icon }) => (
                <a
                  key={label}
                  href={href}
                  target="_blank"
                  rel="noreferrer noopener"
                  aria-label={label}
                  title={label}
                  className="grid size-10 place-items-center rounded-full border border-white/15 text-white/55 transition-all duration-200 hover:-translate-y-0.5 hover:border-brass/70 hover:bg-brass hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass"
                >
                  <Icon aria-hidden="true" className="size-[1.1rem]" strokeWidth={1.8} />
                </a>
              ))}
            </nav>
          </div>
          <div>
            <h2 className="text-sm font-bold">Explora</h2>
            <nav className="mt-4 flex flex-col gap-3 text-sm text-white/50" aria-label="Navegación del pie">
              <a href="#servicios" className="hover:text-white">Servicios</a>
              <a href="#galeria" className="hover:text-white">Galería</a>
              <a href="#valoraciones" className="hover:text-white">Valoraciones</a>
              <a href="/reservar" className="hover:text-white">Reservas</a>
            </nav>
          </div>
          <div>
            <h2 className="text-sm font-bold">Visítanos</h2>
            <div className="mt-4 space-y-3 text-sm text-white/50">
              <p className="flex gap-2"><MapPin className="size-4 shrink-0" />{site.addressLine1} · {site.city}</p>
              <a href={site.phoneHref} className="flex gap-2 transition-colors hover:text-white"><Phone className="size-4 shrink-0" />{site.phoneDisplay}</a>
            </div>
          </div>
          <div>
            <h2 className="text-sm font-bold">Horario</h2>
            <p className="mt-4 flex gap-2 text-sm leading-6 text-white/50"><Clock3 className="mt-1 size-4 shrink-0" /><span>L–V · 9:30–20:00<br />Sábado · 9:00–15:00<br />Domingo · Cerrado</span></p>
          </div>
        </div>
        <div className="mt-10 flex flex-col justify-between gap-3 border-t border-white/10 pt-6 text-xs text-white/35 sm:flex-row">
          <p>© {new Date().getFullYear()} {site.name}. Todos los derechos reservados.</p>
          <p>Privacidad · Cookies · Accesibilidad</p>
        </div>
      </div>
    </footer>
  );
}
