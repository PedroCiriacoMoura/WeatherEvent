import { describe, it, expect } from 'vitest'

import { mount } from '@vue/test-utils'
import App from '../App.vue'
import i18n from '../i18n'

describe('App', () => {
  it('mounts renders properly', () => {
    const wrapper = mount(App, {
      global: {
        plugins: [i18n],
        stubs: { apexchart: true },
      },
    })
    expect(wrapper.text()).toContain('Weather Event')
  })
})
