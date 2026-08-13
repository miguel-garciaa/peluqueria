import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { Gallery } from "@/components/Gallery";

describe("Gallery", () => {
  it("slows the horizontal gallery while it is hovered", () => {
    render(<Gallery />);

    const gallery = screen.getByRole("region", { name: /Pasa el cursor para ralentizar/i });
    fireEvent.pointerEnter(gallery);
    expect(gallery).toHaveAttribute("data-slowed", "true");

    fireEvent.pointerLeave(gallery);
    expect(gallery).toHaveAttribute("data-slowed", "false");
  });

  it("opens a selected photograph with its work details and closes it", async () => {
    render(<Gallery />);

    fireEvent.click(screen.getByRole("button", { name: /Ver detalles: Corte bob pulido/i }));

    expect(screen.getByRole("dialog")).toHaveClass("gallery-lightbox");
    expect(screen.getByRole("dialog")).toHaveAttribute("data-entered", "true");
    expect(screen.getByRole("img", { name: /Corte bob pulido/i }).closest("figure")).toHaveClass("gallery-lightbox-figure");
    expect(screen.getByText("Bob recto con flequillo desfilado")).toBeInTheDocument();
    expect(screen.getByText("Sellado de puntas y acabado pulido")).toBeInTheDocument();
    expect(screen.getByText("Laura Moreno")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Cerrar fotografía" })).toHaveClass("hover:bg-ink", "hover:text-white");

    fireEvent.click(screen.getByRole("button", { name: "Siguiente fotografía" }));
    expect(screen.getByRole("img", { name: /Corte corto texturizado/i })).toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "Cerrar fotografía" }));
    await waitFor(() => expect(screen.queryByRole("dialog")).not.toBeInTheDocument());
  });
});
