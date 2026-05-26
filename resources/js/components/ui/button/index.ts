import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "relative inline-flex items-center justify-center overflow-hidden rounded-lg border border-[#7A4896] px-3 py-1 text-sm! font-medium hover:cursor-pointer disabled:cursor-not-allowed disabled:opacity-60",
  {
    variants: {
      variant: {
        default: "bg-[#7A4896] text-white",
        transparent: "bg-transparent text-evo-black",
        destructive: "border-red-600 bg-red-600 text-white",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
