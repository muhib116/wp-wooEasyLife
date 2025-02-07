<template>
  <Table.Tr>
    <Table.Td class="capitalize">
      {{ index + 1 }}
    </Table.Td>
    <Table.Td>{{ item.ip_phone_or_email }}</Table.Td>
    <Table.Td class="capitalize">
      {{ item.type.replaceAll("_", " ") }}
    </Table.Td>
    <Table.Td>{{ printDate(item.created_at) }}</Table.Td>
    <Table.Td>
      <Button.Primary
        class="ml-auto !bg-red-500 px-2"
        title="Remove from blacklist"
        @onClick="(btn) => removeFromBlacklist(item.id, btn)"
      >
        <Icon name="PhX" size="20" />
      </Button.Primary>
    </Table.Td>
  </Table.Tr>
</template>

<script setup lang="ts">
import { Table, Icon, Button } from "@components";
import { inject } from "vue";
import { printDate } from "@/helper";

const { removeFromBlacklist } = inject("useBlackList");

defineProps<{
  index: number;
  item: {
    ip_phone_or_email: string;
    type: string;
    created_at: string;
  };
}>();
</script>
