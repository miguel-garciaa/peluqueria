import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { Gallery } from "@/components/Gallery";

describe("Gallery", () => {
  it("opens a selected photograph in the large viewer and closes it", async () => {
    render(<Gallery />);

    fireEvent.click(screen.getByRole("button", { name: /Ampliar: Corte bob pulido/i }));

    expect(screen.getByRole("dialog")).toHaveClass("gallery-lightbox");
    expect(screen.getByRole("dialog")).toHaveAttribute("data-entered", "true");
    expect(screen.getByRole("img", { name: /Corte bob pulido/i }).closest("figure")).toHaveClass("gallery-lightbox-figure");
    expect(screen.getByRole("button", { name: "Cerrar fotografía" })).toHaveClass("hover:bg-ink", "hover:text-white");

    fireEvent.click(screen.getByRole("button", { name: "Siguiente fotografía" }));
    expect(screen.getByRole("img", { name: /Corte corto texturizado/i })).toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "Cerrar fotografía" }));
    await waitFor(() => expect(screen.queryByRole("dialog")).not.toBeInTheDocument());
  });
});
