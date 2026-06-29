import type { Component } from 'vue'
import { CalendarDays, LayoutDashboard, User } from '@lucide/vue'

export interface NavItem {
  /** Route name. */
  name: string
  /** i18n key for the label. */
  labelKey: string
  icon: Component
}

/** Primary sidebar navigation. */
export const NAV_ITEMS: NavItem[] = [
  { name: 'dashboard', labelKey: 'nav.dashboard', icon: LayoutDashboard },
  { name: 'events', labelKey: 'nav.events', icon: CalendarDays },
  { name: 'profile', labelKey: 'nav.profile', icon: User },
]
