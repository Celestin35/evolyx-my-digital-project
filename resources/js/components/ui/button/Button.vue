<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import type { ButtonVariants } from "."
import { onBeforeUnmount, onMounted, ref } from "vue"
import { Primitive } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonVariants } from "."
import gsap from "gsap"

interface Props extends PrimitiveProps {
  variant?: ButtonVariants["variant"]
  disableAnimation?: boolean
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  as: "button",
})

const buttonRef = ref<HTMLElement | { $el: HTMLElement } | null>(null)
const currentTextRef = ref<HTMLElement | null>(null)
const nextTextRef = ref<HTMLElement | null>(null)

let tlHover: gsap.core.Timeline | null = null

function playHover() {
  if (props.disableAnimation) return

  tlHover?.play()
}

function reverseHover() {
  if (props.disableAnimation) return

  tlHover?.reverse()
}

onMounted(() => {
  if (props.disableAnimation) return

  const currentText = currentTextRef.value
  const nextText = nextTextRef.value

  if (!currentText || !nextText) return

  gsap.set(currentText, { yPercent: 0, opacity: 1 })
  gsap.set(nextText, { yPercent: 100, opacity: 1 })

  tlHover = gsap.timeline({ paused: true })

  tlHover
    .to(currentText, {
      yPercent: -100,
      duration: 0.3,
      ease: "power2.out",
    })
    .to(
      nextText,
      {
        yPercent: 0,
        duration: 0.3,
        ease: "power2.out",
      },
      "<",
    )
})

onBeforeUnmount(() => {
  tlHover?.kill()
  tlHover = null
})
</script>



<template>
  <Primitive
    ref="buttonRef"
    data-slot="button"
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant }), props.class)"
    @mouseenter="playHover"
    @mouseleave="reverseHover"
  >
    <span v-if="disableAnimation" class="relative z-10 flex items-center justify-center gap-2">
      <slot />
    </span>
    <span v-else class="relative z-10 grid overflow-hidden">
      <span class="invisible flex items-center justify-center gap-2">
        <slot />
      </span>
      <span
        ref="currentTextRef"
        class="button-text-current absolute inset-0 flex items-center justify-center gap-2 whitespace-nowrap"
      >
        <slot />
      </span>
      <span
        ref="nextTextRef"
        class="button-text-next absolute inset-0 flex items-center justify-center gap-2 whitespace-nowrap"
        aria-hidden="true"
      >
        <slot />
      </span>
    </span>
  </Primitive>
</template>
