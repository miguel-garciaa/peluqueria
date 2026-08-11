export function formatSpanishPhone(value: string): string {
  const trimmed = value.trim();
  if (!trimmed) return "";

  let digits = value.replace(/\D/g, "");
  if (digits.startsWith("0034")) {
    digits = digits.slice(4);
  } else if (digits.startsWith("34") && (trimmed.startsWith("+") || digits.length > 9)) {
    digits = digits.slice(2);
  }

  const nationalNumber = digits.slice(0, 9);
  const groups = [
    nationalNumber.slice(0, 3),
    nationalNumber.slice(3, 5),
    nationalNumber.slice(5, 7),
    nationalNumber.slice(7, 9),
  ].filter(Boolean);

  return groups.length ? `+34 ${groups.join(" ")}` : "";
}
