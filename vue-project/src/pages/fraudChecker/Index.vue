<template>
  <Layout>
    <Container>
      <FraudData :data="data" class="max-w-[600px] mx-auto mt-20">
        <FraudForm v-model="phone" @onSubmit="handleFraudCheck" />
      </FraudData>
    </Container>
  </Layout>
</template>

<script setup lang="ts">
import { Layout, Container } from "@layout";
import { checkFraudCustomer } from "@/remoteApi";
import { ref } from "vue";
import FraudData from "./FraudData.vue";
import FraudForm from "./FraudForm.vue";
import { normalizePhoneNumber } from "@/helper";

const phone = ref("");
const data = ref();
const handleFraudCheck = async (btn) => {
  if (!phone.value || normalizePhoneNumber(phone.value).length !== 11) {
    alert("Please enter a valid phone number !");
    return;
  }
  try {
    btn.isLoading = true;
    const payload = {
      data: [
        {
          id: 1,
          phone: phone.value,
        },
      ],
    };

    const { data: _data } = await checkFraudCustomer(payload);
    if (_data?.length) {
      data.value = _data[0];
    }
  } finally {
    btn.isLoading = false;
  }
};
</script>
