<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import SelectEmp from '@/pages/Production/SelectEmp.vue'

const page = usePage<any>()
</script>

<template>
    <SelectEmp v-bind="page.props" />
</template>
