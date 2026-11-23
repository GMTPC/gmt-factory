<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import LineMaster from '@/pages/Production/LineMaster.vue'

const page = usePage<any>()
</script>

<template>
    <LineMaster v-bind="page.props" />
</template>
