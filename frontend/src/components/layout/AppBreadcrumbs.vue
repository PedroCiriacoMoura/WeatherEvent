<script setup lang="ts">
import { computed } from 'vue'
import { ChevronRight } from '@lucide/vue'
import { RouterLink, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'

const route = useRoute()
const { t } = useI18n()

const crumbs = computed(() => route.meta.breadcrumbs ?? [])
</script>

<template>
  <nav v-if="crumbs.length" :aria-label="t('nav.breadcrumb')" class="hidden sm:block">
    <ol class="flex items-center gap-1.5 text-sm">
      <li
        v-for="(crumb, index) in crumbs"
        :key="`${crumb.labelKey}-${index}`"
        class="flex items-center gap-1.5"
      >
        <ChevronRight v-if="index > 0" class="size-3.5 text-muted-foreground/60" aria-hidden="true" />
        <RouterLink
          v-if="crumb.name && index < crumbs.length - 1"
          :to="{ name: crumb.name }"
          class="text-muted-foreground transition-colors hover:text-foreground"
        >
          {{ t(crumb.labelKey) }}
        </RouterLink>
        <span
          v-else
          :class="index === crumbs.length - 1 ? 'font-medium text-foreground' : 'text-muted-foreground'"
          :aria-current="index === crumbs.length - 1 ? 'page' : undefined"
        >
          {{ t(crumb.labelKey) }}
        </span>
      </li>
    </ol>
  </nav>
</template>
