<template>
  <FormPage :page="page" formId="customer-form" @submit="form.post('/cp/customers')">
    <Card title="Personal Information">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.first_name" label="First Name" :error="form.errors.first_name" placeholder="First name" required />
        <Input v-model="form.last_name" label="Last Name" :error="form.errors.last_name" placeholder="Last name" required />
        <Input v-model="form.email" label="Email" :error="form.errors.email" type="email" placeholder="email@example.com" required />
        <Input v-model="form.phone_number" label="Phone" type="tel" placeholder="+39..." />
        <Input v-model="form.date_of_birth" label="Date of Birth" type="date" />
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Gender</label>
          <Select v-model="form.gender" :options="genderOptions" placeholder="Select gender" />
        </div>
      </div>
    </Card>

    <Card title="Account">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.password" label="Password" :error="form.errors.password" type="password" placeholder="Password" required />
        <Input v-model="form.password_confirmation" label="Confirm Password" type="password" placeholder="Confirm password" required />
      </div>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Switch v-model="form.is_enabled" label="Active" />
      </Card>
      <Card title="Customer Group">
        <Select v-model="form.customer_group_id" :options="groupOptions" placeholder="Select group" />
      </Card>
    </template>
  </FormPage>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Card, Input, Select, Switch } from '@cartino/ui'
import FormPage from '../../components/FormPage.vue'

const props = defineProps({ page: Object, customerGroups: Array })

const form = useForm({
  first_name: '', last_name: '', email: '', phone_number: '',
  date_of_birth: '', gender: '', password: '', password_confirmation: '',
  is_enabled: true, customer_group_id: null,
})

const genderOptions = [
  { value: '', label: 'Select gender' },
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
]

const groupOptions = computed(() => [
  { value: null, label: 'No group' },
  ...(props.customerGroups || []).map(g => ({ value: g.id, label: g.name })),
])
</script>
