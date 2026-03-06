<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'flex flex-col gap-6 rounded-2xl border border-white/50 bg-white/50 py-6 shadow-sm backdrop-blur-md',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>