<template>
  <div class="bg-white rounded-lg shadow-lg">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">New Requisition</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <PhX :size="24" weight="bold" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <!-- User Type -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            User Type <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-3">
            <button
              v-for="type in userTypes"
              :key="type.value"
              type="button"
              @click="selectedUserType = type.value"
              :class="[
                'flex items-center px-4 py-2 rounded-lg border-2 transition-colors',
                selectedUserType === type.value
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                  : 'border-gray-200 hover:bg-gray-50'
              ]"
            >
              <PhCheckCircle
                v-if="selectedUserType === type.value"
                :size="20"
                class="mr-2 text-emerald-500"
                weight="fill"
              />
              {{ type.label }}
            </button>
          </div>
        </div>

        <!-- Category -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Category <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <select
              v-model="category"
              class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            >
              <option value="" disabled>Select</option>
              <option v-for="cat in categories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
            <PhCaretDown
              :size="16"
              class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"
            />
          </div>
        </div>

        <!-- Requisition Name -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Requisition Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="requisitionName"
            type="text"
            placeholder="Type here"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
          />
        </div>

        <!-- Description -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Description:
          </label>
          <div class="border border-gray-300 rounded-lg p-4">
            <div class="mb-3">
              <textarea
                v-model="description"
                rows="3"
                class="w-full resize-none border-0 focus:ring-0 p-0"
                :placeholder="defaultDescription"
              ></textarea>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="variable in variables"
                :key="variable.id"
                type="button"
                @click="insertVariable(variable)"
                class="inline-flex items-center px-2 py-1 bg-gray-100 rounded text-sm text-gray-700 hover:bg-gray-200"
              >
                <component :is="getVariableIcon(variable.type)" :size="16" class="mr-1" />
                {{ variable.id }}
              </button>
              <button
                type="button"
                @click="showVariableForm = true"
                class="inline-flex items-center px-2 py-1 text-emerald-600 hover:bg-emerald-50 rounded text-sm"
              >
                <PhPlus :size="16" class="mr-1" />
                New Variable
              </button>
            </div>
          </div>
        </div>

        <!-- Required Text -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Required Text
          </label>
          <input
            v-model="requiredText"
            type="text"
            placeholder="File Opening Sheet"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
          />
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-white bg-emerald-600 rounded-lg hover:bg-emerald-700"
          >
            Add Requisition
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  PhX,
  PhCheckCircle,
  PhCaretDown,
  PhTextT,
  PhNumberCircle,
  PhCalendar,
  PhEnvelopeSimple,
  PhPlus
} from '@phosphor-icons/vue'

const userTypes = [
  { label: 'Purchase', value: 'purchase' },
  { label: 'Mortgage', value: 'mortgage' },
  { label: 'Response', value: 'response' }
]

const categories = ['Category 1', 'Category 2', 'Category 3']

const variables = [
  { id: 'NAME', type: 'text' },
  { id: 'AMOUNT', type: 'number' },
  { id: 'SIGNING_DATE', type: 'date' },
  { id: 'EMAIL', type: 'email' },
  { id: 'PRICE', type: 'number' }
]

const defaultDescription = 'The Agreement of Purchase and Sale indicates that the {{NAME}} shall assume the tenancy agreement on {{SIGNING_DATE}} shall assume the tenancy agreements.'

const selectedUserType = ref('purchase')
const category = ref('')
const requisitionName = ref('')
const description = ref('')
const requiredText = ref('')
const showVariableForm = ref(false)

const getVariableIcon = (type) => {
  switch (type) {
    case 'text': return PhTextT
    case 'number': return PhNumberCircle
    case 'date': return PhCalendar
    case 'email': return PhEnvelopeSimple
    default: return PhTextT
  }
}

const insertVariable = (variable) => {
  const insertion = `{{${variable.id}}}`
  const textarea = document.querySelector('textarea')
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  
  description.value = 
    description.value.substring(0, start) +
    insertion +
    description.value.substring(end)
}

const handleSubmit = () => {
  // Handle form submission
  console.log({
    userType: selectedUserType.value,
    category: category.value,
    requisitionName: requisitionName.value,
    description: description.value,
    requiredText: requiredText.value
  })
}
</script>