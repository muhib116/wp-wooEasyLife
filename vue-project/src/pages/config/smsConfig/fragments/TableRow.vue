<template>
    <Table.Tr>
        <Table.Td>{{ index + 1 }}</Table.Td>
        <Table.Td class="capitalize font-bold">
            {{ item.is_active ? 'Yes' : 'No' }}
        </Table.Td>
        <Table.Td class="capitalize">
            {{ item.status.replace('wc-', '').replaceAll('-', ' ') }}
        </Table.Td>
        <Table.Td class="capitalize">
            {{ item.message_for }}
        </Table.Td>
        <Table.Td>
            {{ item.phone_number || 'n/a' }}
        </Table.Td>
        <Table.Td>
            {{ item.message }}
        </Table.Td>
        <Table.Td class="text-right">
            <ThreeDotActionButton
                :edit="() => toggleEdit = true"
                :delete="(btn) => handleDeleteSMS(item.id, btn)"
            />
        </Table.Td>
    </Table.Tr>

    <Modal 
        v-model="toggleEdit"
        @close="toggleEdit = false"
        title="Update Message"
    >
        <Edit
            :item="item"
            @onUpdate="toggleEdit = false"
        />
    </Modal>
</template>

<script setup lang="ts">
    import { inject, ref } from 'vue'
    import { Table, ThreeDotActionButton, Modal, Switch } from '@components'
    import Edit from '../Edit.vue'


    defineProps<{
        index: number
        item: {
            status: string
            message_for: string
            phone_number: string
            message: string
            id: number
        }
    }>()

    const toggleEdit = ref(false)
    const {
        handleDeleteSMS
    } = inject('useSmsConfig')
</script>