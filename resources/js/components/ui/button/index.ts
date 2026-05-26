import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "relative inline-flex items-center justify-center overflow-hidden rounded-xl border border-[#7A4896] px-4 py-2 text-base font-medium hover:cursor-pointer",
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
