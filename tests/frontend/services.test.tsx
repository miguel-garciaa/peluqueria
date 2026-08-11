import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Services } from "@/components/Services";
import type { BookingCatalogService } from "@/types";

const databaseServices: BookingCatalogService[] = [
  { id: "cut", name: "Corte & Peinado", description: "Descripción actualizada", durationMinutes: 50, priceFrom: 1, isCustom: false },
];

describe("Services", () => {
  it("uses the current price and duration received from the database", () => {
    render(<Services onBook={vi.fn()} catalogServices={databaseServices} />);

    expect(screen.getByText("Desde 1 €")).toBeInTheDocument();
    expect(screen.getByText("50 min")).toBeInTheDocument();
    expect(screen.queryByText("Desde 35 €")).not.toBeInTheDocument();
  });

  it("shows newly created services and their database description", () => {
    const newlyCreatedService = {
      id: "new-treatment",
      name: "Tratamiento nuevo",
      description: "Descripción administrada desde Filament.",
      durationMinutes: 40,
      priceFrom: 29,
      isCustom: false,
    };

    render(<Services onBook={vi.fn()} catalogServices={[newlyCreatedService]} />);

    expect(screen.getByRole("button", { name: "Ver detalles de Tratamiento nuevo" })).toBeInTheDocument();
    expect(screen.getByText("Descripción administrada desde Filament.")).toBeInTheDocument();
  });

  it("opens service details and books the selected service", () => {
    vi.useFakeTimers();
    const onBook = vi.fn();
    render(<Services onBook={onBook} />);

    fireEvent.click(screen.getByRole("button", { name: "Ver detalles de Balayage" }));
    expect(screen.getByRole("dialog")).toBeVisible();
    expect(screen.getByText("Diseño de color personalizado")).toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "Reservar este servicio" }));
    vi.advanceTimersByTime(180);
    expect(onBook).toHaveBeenCalledWith("balayage");
    vi.useRealTimers();
  });

  it("shows the complete service detail without an internal scroll area", () => {
    render(<Services onBook={vi.fn()} />);

    fireEvent.click(screen.getByRole("button", { name: "Ver detalles de Ritual Capilar" }));

    const dialog = screen.getByRole("dialog");
    expect(dialog.querySelector(".overflow-y-auto")).not.toBeInTheDocument();
  });

  it("keeps the booking action available for every service", () => {
    render(<Services onBook={vi.fn()} />);

    for (const serviceName of ["Corte & Peinado", "Balayage", "Keratina & Brillo", "Barba & Estilo", "Ritual Capilar", "Peinado de Evento"]) {
      fireEvent.click(screen.getByRole("button", { name: `Ver detalles de ${serviceName}` }));
      expect(screen.getByRole("button", { name: "Reservar este servicio" })).toBeVisible();
      fireEvent.click(screen.getByRole("button", { name: "Cerrar detalles del servicio" }));
    }
  });
});
