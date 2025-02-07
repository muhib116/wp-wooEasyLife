<template>
    <div>
        <div class="flex gap-2">
            <strong>Name: </strong>
            <div>
                {{ address.first_name }}
                {{ address.last_name }}
            </div>
        </div>
        <div v-if="address.company" class="flex gap-2">
            <strong>Company: </strong>
            <div>
                {{ address.company }}
            </div>
        </div>
        <div class="flex gap-2">
            <strong>Address: </strong>
            <div>
                {{ address.address_1 }}
                <br />
                {{ address.address_2 }}
            </div>
        </div>
        <div v-if="address.city" class="flex gap-2">
            <strong>City: </strong>
            <div>
                {{ address.city }}
            </div>
        </div>
        <div v-if="address.state" class="flex gap-2">
            <strong>State: </strong>
            <div>
                {{ address.state }}
            </div>
        </div>
        <div v-if="address.postcode" class="flex gap-2">
            <strong>Post Code: </strong>
            <div>
                {{ address.postcode }}
            </div>
        </div>
        <div v-if="address.country" class="flex gap-2">
            <strong>Country: </strong>
            <div>
                {{ address.country }}
            </div>
        </div>
        
        <div 
            v-if="address.email && address.type == 'billing'" 
            class="flex gap-2"
        >
            <strong>Email: </strong>
            <div>
                {{ address.email }}
            </div>
        </div>
        <div
            v-if="address.phone && address.type == 'billing'"  
            class="flex gap-2"
        >
            <strong>Phone: </strong>
            <div>
                {{ address.phone || 'n/a' }}
            </div>
        </div>
        <div v-if="address.type == 'billing'" class="flex gap-2">
            <strong>Transaction Id: </strong>
            <div>
                {{ address.transaction_id || 'n/a' }}
            </div>
        </div>
        <div v-if="address.type == 'shipping'" class="flex gap-2">
            <strong>Note: </strong>
            <div>
                {{ address.customer_note || 'n/a' }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    defineProps<{
        address: object
    }>()
</script>