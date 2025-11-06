import Native from './ui/Native.vue'
import Primary from './ui/Primary.vue'
// @ts-ignore: Vue SFC module lacking declaration file
import Outline from './ui/Outline.vue'

const Button: Record<string, unknown> = {
    Native: Native as unknown,
    Primary: Primary as unknown,
    Outline: Outline as unknown
}

export default Button