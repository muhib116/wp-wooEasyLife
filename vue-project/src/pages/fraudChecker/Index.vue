<template>
  <Layout>
    <Container>
      <FraudData
        v-if="(userData?.remaining_order) > 0"
        :data="data" class="max-w-[600px] mx-auto mt-20"
      >
        <FraudForm v-model="phone" @onSubmit="btn => {
          handleFraudCheck(phone, btn)
        }"/>
      </FraudData>
      <div v-else >
          <MessageBox
            title="Insufficient balance! Access denied."
            type="danger"
          />
      </div>
    </Container>
  </Layout>
</template>

<script setup lang="ts">
import { Layout, Container } from "@layout";
import { MessageBox } from '@components'
import { ref, inject } from "vue";
import FraudData from "./FraudData.vue";
import FraudForm from "./FraudForm.vue";
import { useFraudChecker } from './useFraudChecker'

const { userData }  = inject("useServiceProvider")
const phone = ref('')
const { handleFraudCheck, data } = useFraudChecker()
</script>
