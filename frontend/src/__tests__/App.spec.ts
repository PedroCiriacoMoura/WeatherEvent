import { describe, it, expect } from 'vitest'

import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import App from '../App.vue'
import i18n from '../i18n'

describe('App', () => {
  it('mounts the application shell without errors', () => {
    const wrapper = mount(App, {
      global: {
        plugins: [createPinia(), i18n],
        stubs: { RouterView: true, Toaster: true },
      },
    })
    expect(wrapper.exists()).toBe(true)
  })
})
