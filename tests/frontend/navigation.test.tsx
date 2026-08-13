import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Navbar } from "@/components/Navbar";

describe("Navbar", () => {
  it("uses a full-width shell and keeps the compact menu through tablet widths", () => {
    render(<Navbar />);

    expect(screen.getByRole("navigation", { name: "Navegación principal" })).toHaveClass("navbar-shell");
    expect(screen.getByRole("button", { name: "Abrir menú" })).toHaveClass("hidden", "md:grid", "xl:hidden");
    expect(screen.getAllByRole("link", { name: "Inicio 01" })[0].parentElement).toHaveClass("xl:flex");
    expect(screen.getByRole("navigation", { name: "Navegación móvil" })).toHaveClass("md:hidden");
    const accountLink = screen.getAllByRole("link", { name: "Iniciar sesión con Google" })[0];
    expect(accountLink).toHaveAttribute("href", "/auth/google");
    expect(accountLink.querySelector("svg")).toBeInTheDocument();
    expect(accountLink).not.toHaveTextContent(/^GCuenta$/);
  });

  it("uses an app-like five-item bottom navigation on phones", () => {
    const onBook = vi.fn();
    render(<Navbar onBook={onBook} />);

    const mobileNavigation = screen.getByRole("navigation", { name: "Navegación móvil" });
    expect(mobileNavigation.getElementsByClassName("mobile-bottom-nav__item")).toHaveLength(5);
    expect(mobileNavigation).toHaveTextContent("Inicio");
    expect(mobileNavigation).toHaveTextContent("Servicios");
    expect(mobileNavigation).toHaveTextContent("Reservar");
    expect(mobileNavigation).toHaveTextContent("Galería");
    expect(mobileNavigation).toHaveTextContent("Cuenta");

    fireEvent.click(screen.getByRole("button", { name: "Reservar" }));
    expect(onBook).toHaveBeenCalledOnce();
  });

  it("opens account actions from the mobile tab bar", () => {
    render(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} csrfToken="csrf-test" />);

    fireEvent.click(screen.getByRole("button", { name: "Cuenta" }));

    expect(screen.getByRole("dialog", { name: "Ana López" })).toBeInTheDocument();
    expect(screen.getAllByRole("link", { name: "Mis citas" }).at(-1)).toHaveAttribute("href", "/mis-citas");
    const logoutButton = screen.getAllByRole("button", { name: "Cerrar sesión" }).at(-1);
    expect(logoutButton?.closest("form")).toHaveAttribute("action", "/logout");
    expect(logoutButton?.closest("form")?.querySelector('input[name="_token"]')).toHaveValue("csrf-test");
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
