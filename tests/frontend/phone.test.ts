import { describe, expect, it } from "vitest";
import { formatSpanishPhone } from "@/lib/phone";

describe("formatSpanishPhone", () => {
  it.each([
    "600123456",
    "600 123 456",
    "+34600123456",
    "+34 600 12 34 56",
    "0034 600 123 456",
  ])("formats %s using the Spanish international layout", (input) => {
    expect(formatSpanishPhone(input)).toBe("+34 600 12 34 56");
  });

  it("formats the number progressively while typing", () => {
    expect(formatSpanishPhone("6")).toBe("+34 6");
    expect(formatSpanishPhone("6001")).toBe("+34 600 1");
    expect(formatSpanishPhone("6001234")).toBe("+34 600 12 34");
  });
});
