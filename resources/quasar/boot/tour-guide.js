import { TourGuideClient, tourguide } from '@sjmc11/tourguidejs'

export default async ({ Vue, app, store }) => {
  await store.dispatch('tourGuide/initStore')

  const i18n = app.i18n
  let tourGuide

  const stepGroups = require('../assets/tour-guide.json')

  // const steps = []
  // let stepGroup

  // for (const k in stepGroups) {
  //   stepGroup = stepGroups[k].filter(v => !!v)

  //   if (stepGroup && stepGroup.length) {
  //     stepGroup.forEach(v => {
  //       steps.push({
  //         ...v,
  //         group: k
  //       })
  //     })
  //   }
  // }

  tourGuide = new TourGuideClient({
    steps: [],
    exitOnEscape: false,
    exitOnClickOutside: false,
    completeOnFinish: false,
    showStepDots: false,
    debug: false
  })


  tourGuide.onFinish(() => {
    store.dispatch('tourGuide/finish', tourGuide.group)
  })

  const callStepCallback = (obj) => {
    if (obj.options) {
      tourGuide.setOptions(obj.options)
    }

    if (obj.methods) {
      if (obj.methods.refresh) {
        setTimeout(() => {
          tourGuide.refresh()
        }, 10)
      }

      if (obj.methods.updatePositions) {
        setTimeout(() => {
          tourGuide.updatePositions()
        }, 15)
      }
    }

    if (obj.store) {
      if (obj.store.finish) {
        setTimeout(() => {
          store.dispatch('tourGuide/finish', obj.group)
        }, 10)
      }
    }

    if (obj.scrollIntoView) {
      setTimeout(() => {
        const scrollElement = document.querySelector(obj.scrollIntoView)

        if (scrollElement) {
          scrollElement.scrollIntoView({
            behavior: 'smooth'
          })
        }
      }, 15)

    }
  }

  const buildSteps = (group) => {
    if (stepGroups[group]) {
      const result = []

      stepGroups[group].forEach(v => {
        const obj = {
          group,
          content: i18n.t(v.content),
          target: v.target,
        }

        if (typeof v.title === 'string') {
          obj.title = i18n.t(v.title)
        } else if (v.title && v.title.text) {
          const titlePayloadResult = {}
          const titlePayloads = v.title.payload || {}

          for (const k in titlePayloads) {
            titlePayloadResult[k] = i18n.t(titlePayloads[k])
          }

          obj.title = i18n.t(v.title.text, titlePayloadResult)
        }

        if (v.beforeEnter) {
          v.beforeEnter.group = group

          obj.beforeEnter = () => {
            return callStepCallback(v.beforeEnter)
          }
        }

        if (v.afterEnter) {
          v.afterEnter.group = group

          obj.afterEnter = () => {
            return callStepCallback(v.afterEnter)
          }
        }

        result.push(obj)
      })

      return result
    }

    return []
  }

  tourGuide.open = (group) => {
    return
    if (stepGroups[group]) {
      let steps = []

      if (group === 'default') {
        steps = [
          ...buildSteps('default'),
          ...buildSteps('index'),
        ]
      } else {
        steps = buildSteps(group)
      }

      steps = steps.filter(v => {
        try {
          if (document.querySelector(v.target)) {
            return true
          }
        } catch (err) {}

        return false
      })

      tourGuide.setOptions({ steps })

      setTimeout(() => {
        tourGuide.start(group)

        if (group === 'default') {
          store.dispatch('tourGuide/finish', 'index')
        }
      }, 10)
    }
  }

  Vue.prototype.$tourGuide = tourGuide
}
