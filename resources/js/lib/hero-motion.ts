export type HeroNavigationType = "navigate" | "reload" | "back_forward";
export type HeroMotionMode = "play" | "settled";

export function getHeroMotionMode(navigationType: HeroNavigationType, reducedMotion: boolean): HeroMotionMode {
  return !reducedMotion && navigationType === "navigate" ? "play" : "settled";
}
