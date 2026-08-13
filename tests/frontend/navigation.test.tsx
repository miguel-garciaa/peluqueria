import { fireEvent, render, screen, waitFor, within } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { MobileAccountPage } from "@/components/MobileAccountPage";
import { Navbar } from "@/components/Navbar";

describe("Navbar", () => {
  it("uses a full-width shell and keeps the compact menu through tablet widths", () => {
    const { container } = render(<Navbar />);

    expect(screen.getByRole("navigation", { name: "Navegación principal" })).toHaveClass("navbar-shell");
    expect(screen.getByRole("button", { name: "Abrir menú" })).toHaveClass("grid", "xl:hidden");
    expect(screen.getAllByRole("link", { name: "Inicio 01" })[0].parentElement).toHaveClass("xl:flex");
    expect(screen.queryByRole("navigation", { name: "Navegación móvil" })).not.toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Abrir menú" })).toHaveClass("grid");
    expect(container.querySelector(".navbar-accent-line")).toHaveClass("hidden", "md:block");
    expect(container.querySelector(".scroll-progress")).toHaveClass("hidden", "md:block");
    const accountMenu = container.querySelector("details");
    expect(accountMenu).not.toBeNull();
    fireEvent.click(within(accountMenu as HTMLElement).getByText("Cuenta"));
    const accountLink = within(accountMenu as HTMLElement).getByRole("link", { name: "Iniciar sesión con Google" });
    expect(accountLink).toHaveAttribute("href", "/auth/google");
    expect(accountLink.querySelector("svg")).toBeInTheDocument();
    expect(accountMenu).toHaveAttribute("open");
  });

  it("uses an app-like six-item bottom navigation on phones", () => {
    const onMobileViewChange = vi.fn();
    render(<Navbar onMobileViewChange={onMobileViewChange} appMode />);

    const mobileNavigation = screen.getByRole("navigation", { name: "Navegación móvil" });
    expect(mobileNavigation.getElementsByClassName("mobile-bottom-nav__item")).toHaveLength(6);
    expect(mobileNavigation).toHaveTextContent("Inicio");
    expect(mobileNavigation).toHaveTextContent("Servicios");
    expect(mobileNavigation).toHaveTextContent("Equipo");
    expect(mobileNavigation).toHaveTextContent("Reservar");
    expect(mobileNavigation).toHaveTextContent("Galería");
    expect(mobileNavigation).toHaveTextContent("Cuenta");

    fireEvent.click(screen.getByRole("link", { name: "Equipo" }));
    expect(onMobileViewChange).toHaveBeenLastCalledWith("equipo");
    fireEvent.click(screen.getByRole("link", { name: "Reservar" }));
    expect(onMobileViewChange).toHaveBeenLastCalledWith("reservar");
  });

  it("links the account tab to its own app page", () => {
    render(<Navbar activeMobileView="cuenta" appMode />);

    expect(screen.getByRole("link", { name: "Cuenta" })).toHaveAttribute("href", "/cuenta");
    expect(screen.getByRole("link", { name: "Cuenta" })).toHaveAttribute("aria-current", "page");
  });

  it("shows the authenticated account and a secure logout form", () => {
    render(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} csrfToken="csrf-test" />);

    expect(screen.getByRole("group")).toBeInTheDocument();
    expect(screen.getAllByText("ana@example.com")[0]).toBeInTheDocument();
    expect(screen.getAllByRole("link", { name: "Mis citas" })[0]).toHaveAttribute("href", "/mis-citas");
    const logoutButtons = screen.getAllByRole("button", { name: "Cerrar sesión" });
    expect(logoutButtons[0].closest("form")).toHaveAttribute("action", "/logout");
    expect(logoutButtons[0].closest("form")?.querySelector('input[name="_token"]')).toHaveValue("csrf-test");
  });

  it("replaces booking with the control panel action for administrators", () => {
    const { rerender } = render(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} />);
    expect(screen.queryByRole("link", { name: "Panel de control" })).not.toBeInTheDocument();
    expect(screen.getAllByRole("link", { name: "Reservar cita" })).not.toHaveLength(0);

    rerender(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null, isAdmin: true }} />);
    expect(screen.getAllByRole("link", { name: "Panel de control" })[0]).toHaveAttribute("href", "/admin");
    expect(screen.queryByRole("link", { name: "Mis citas" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "Reservar cita" })).not.toBeInTheDocument();
  });

  it("centers the primary action in the mobile menu", () => {
    render(<Navbar />);

    const mobileBookingAction = screen.getAllByRole("link", { name: "Reservar cita" }).find((link) => link.parentElement?.classList.contains("justify-center"));
    expect(mobileBookingAction).toHaveClass("mx-auto", "w-fit");
    expect(mobileBookingAction?.parentElement).toHaveClass("flex", "w-full", "justify-center");
  });

  it("scrolls smoothly to the selected section", () => {
    const matchMedia = vi.spyOn(window, "matchMedia").mockReturnValue({
        matches: false,
        media: "(prefers-reduced-motion: reduce)",
        onchange: null,
        addListener: () => {},
        removeListener: () => {},
        addEventListener: () => {},
        removeEventListener: () => {},
        dispatchEvent: () => false,
      });
    const scrollIntoView = vi.spyOn(HTMLElement.prototype, "scrollIntoView");

    render(
      <>
        <Navbar />
        <section id="inicio" />
        <section id="servicios" />
        <section id="equipo" />
        <section id="galeria" />
        <section id="reservas" />
      </>,
    );

    fireEvent.click(screen.getAllByRole("link", { name: "Servicios 02" })[0]);

    expect(scrollIntoView).toHaveBeenCalledWith({ behavior: "smooth", block: "start" });
    scrollIntoView.mockRestore();
    matchMedia.mockRestore();
  });

  it("uses an instant scroll when reduced motion is preferred", () => {
    const matchMedia = vi.spyOn(window, "matchMedia").mockReturnValue({
      matches: true,
      media: "(prefers-reduced-motion: reduce)",
      onchange: null,
      addListener: () => {},
      removeListener: () => {},
      addEventListener: () => {},
      removeEventListener: () => {},
      dispatchEvent: () => false,
    });
    const scrollIntoView = vi.spyOn(HTMLElement.prototype, "scrollIntoView");

    render(
      <>
        <Navbar />
        <section id="inicio" />
        <section id="servicios" />
        <section id="equipo" />
        <section id="galeria" />
        <section id="reservas" />
      </>,
    );

    fireEvent.click(screen.getAllByRole("link", { name: "Galería 04" })[0]);

    expect(scrollIntoView).toHaveBeenCalledWith({ behavior: "auto", block: "start" });
    scrollIntoView.mockRestore();
    matchMedia.mockRestore();
  });
});

describe("MobileAccountPage", () => {
  it("shows Google sign-in as a separate action for guests", () => {
    render(<MobileAccountPage currentUser={null} csrfToken="csrf-test" />);

    expect(screen.getByRole("heading", { name: "Cuenta" })).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Iniciar sesión con Google" })).toHaveAttribute("href", "/auth/google");
  });

  it("shows appointments and a secure logout form for the signed-in user", () => {
    render(<MobileAccountPage currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} csrfToken="csrf-test" />);

    expect(screen.getByRole("heading", { name: "Cuenta" })).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Mis citas" })).toHaveAttribute("href", "/mis-citas");
    const logoutButton = screen.getByRole("button", { name: "Cerrar sesión" });
    expect(logoutButton.closest("form")).toHaveAttribute("action", "/logout");
    expect(logoutButton.closest("form")?.querySelector('input[name="_token"]')).toHaveValue("csrf-test");
  });

  it("explains when push notifications are not configured", async () => {
    render(<MobileAccountPage currentUser={{ name: "Ana LÃ³pez", email: "ana@example.com", phone: null, avatarUrl: null }} csrfToken="csrf-test" />);

    expect(screen.getByRole("heading", { name: "Avisos de tus citas" })).toBeInTheDocument();
    await waitFor(() => expect(screen.getByText(/Instala la app y .*brela desde la pantalla de inicio/i)).toBeInTheDocument());
  });
});
