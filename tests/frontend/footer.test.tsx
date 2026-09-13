import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { Footer } from "@/components/Footer";

describe("Footer", () => {
  it("shows accessible links to the salon social networks", () => {
    render(<Footer />);

    for (const name of ["Instagram", "X", "Facebook", "TikTok", "WhatsApp"]) {
      const link = screen.getByRole("link", { name });

      expect(link).toHaveAttribute("target", "_blank");
      expect(link).toHaveAttribute("rel", expect.stringContaining("noopener"));
      expect(link).toHaveAttribute("href", expect.stringMatching(/^https:\/\//));
    }
  });
});
