<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import WipDetail from '@/pages/Production/WipDetail.vue'

const page = usePage<any>()
</script>

<template>
    <WipDetail v-bind="page.props" />
</template>
