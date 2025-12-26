import { createMongoAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'

export default function (app) {
  const userAbilityRules = useCookie('userAbilityRules')
  const initialAbility = createMongoAbility(userAbilityRules.value ?? [{ action: 'manage', subject: 'article' }])

  app.use(abilitiesPlugin, initialAbility, {
    useGlobalProperties: true,
  })
}
