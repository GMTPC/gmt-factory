<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import PreviousShift from '@/pages/Production/PreviousShift.vue'

const page = usePage<any>()
</script>

<template>
    <PreviousShift v-bind="page.props" />
</template>
