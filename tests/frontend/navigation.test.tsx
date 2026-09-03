import { fireEvent, render, screen, within } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Navbar } from "@/components/Navbar";

describe("Navbar", () => {
  it("uses the responsive website navigation without an app navigation bar", () => {
    const { container } = render(<Navbar />);

    expect(screen.getByRole("navigation", { name: "Navegación principal" })).toHaveClass("navbar-shell");
    expect(screen.getByRole("button", { name: "Abrir menú" })).toHaveClass("grid", "xl:hidden");
    expect(screen.queryByRole("navigation", { name: "Navegación móvil" })).not.toBeInTheDocument();
    expect(container.querySelector(".mobile-bottom-nav")).not.toBeInTheDocument();
    expect(screen.queryByText("Instalar app")).not.toBeInTheDocument();
  });

  it("offers Google sign-in from the account menu", () => {
    const { container } = render(<Navbar />);
    const accountMenu = container.querySelector("details");

    expect(accountMenu).not.toBeNull();
    fireEvent.click(within(accountMenu as HTMLElement).getByText("Cuenta"));
    expect(within(accountMenu as HTMLElement).getByRole("link", { name: "Iniciar sesión con Google" })).toHaveAttribute("href", "/auth/google");
    expect(accountMenu).toHaveAttribute("open");
  });

  it("shows the authenticated account and a secure logout form", () => {
    render(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} csrfToken="csrf-test" />);

    expect(screen.getAllByText("ana@example.com")[0]).toBeInTheDocument();
    expect(screen.getAllByRole("link", { name: "Mis citas" })[0]).toHaveAttribute("href", "/mis-citas");
    const logoutButton = screen.getAllByRole("button", { name: "Cerrar sesión" })[0];
    expect(logoutButton.closest("form")).toHaveAttribute("action", "/logout");
    expect(logoutButton.closest("form")?.querySelector('input[name="_token"]')).toHaveValue("csrf-test");
  });

  it("replaces booking with the control panel action for administrators", () => {
    const { rerender } = render(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null }} />);
    expect(screen.queryByRole("link", { name: "Panel de control" })).not.toBeInTheDocument();

    rerender(<Navbar currentUser={{ name: "Ana López", email: "ana@example.com", phone: null, avatarUrl: null, isAdmin: true }} />);
    expect(screen.getAllByRole("link", { name: "Panel de control" })[0]).toHaveAttribute("href", "/admin");
    expect(screen.queryByRole("link", { name: "Mis citas" })).not.toBeInTheDocument();
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

    render(<><Navbar /><section id="inicio" /><section id="servicios" /><section id="equipo" /><section id="galeria" /><section id="reservas" /></>);
    fireEvent.click(screen.getAllByRole("link", { name: "Servicios 02" })[0]);

    expect(scrollIntoView).toHaveBeenCalledWith({ behavior: "smooth", block: "start" });
    scrollIntoView.mockRestore();
    matchMedia.mockRestore();
  });
});
