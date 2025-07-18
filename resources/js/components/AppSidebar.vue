<script setup>
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, SignalMedium, User, Users2, CalendarCheck } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const { props } = usePage();
const authUser = props.auth;

// Helper function to check permissions
const hasPermission = (permission) => {
  return authUser?.permissions?.some(p => p.name === permission);
};

// Main navigation items with permission checks
const mainNavItems = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
    permission: 'view_records'
  },
  {
    title: 'Records',
    href: '/records',
    icon: SignalMedium,
    permission: 'view_records'
  },
  {
    title: 'Project Outlooks',
    href: '/project-outlooks',
    icon: CalendarCheck,
    permission: 'view_project_outlooks'
  },
  {
    title: 'User Management',
    href: '/users',
    icon: Users2,
    permission: 'manage_users'
  }
].filter(item => hasPermission(item.permission));

// State coordinates for regional dashboards
const stateCoordinates = [
  'abia', 'adamawa', 'akwaibom', 'anambra', 'bauchi', 'bayelsa', 'benue', 'borno',
  'crossriver', 'delta', 'ebonyi', 'edo', 'ekiti', 'enugu', 'gombe', 'imo',
  'jigawa', 'kaduna', 'kano', 'katsina', 'kebbi', 'kogi', 'kwara', 'lagos',
  'nasarawa', 'niger', 'ogun', 'ondo', 'osun', 'oyo', 'plateau', 'rivers',
  'sokoto', 'taraba', 'yobe', 'zamfara', 'fct'
];

// Add state-specific dashboards based on permissions
if (hasPermission('view_records')) {
  const accessibleStates = authUser?.state_permissions?.map(s => s.name.toLowerCase().replace(' ', '')) || [];
    
  stateCoordinates.forEach(state => {
    if (accessibleStates.includes(state.toLowerCase())) {
      mainNavItems.push({
        title: state,
        href: `/dashboard/${state}`,
        icon: SignalMedium,
        permission: 'view_records'
      });
    }
  });

  // Add Nigeria dashboard for admins and those with multi-state access
  if (hasPermission('manage_state_records') || accessibleStates.length > 1) {
    mainNavItems.push({
      title: 'Nigeria',
      href: '/dashboard/nigeria',
      icon: SignalMedium,
      permission: 'view_records'
    });
  }
}

// Footer navigation items
const footerNavItems = [
  {
    title: 'Profile',
    href: '/profile',
    icon: User
  }
  // Add more footer items as needed
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
<!-- <script setup >
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, SignalMedium, User, Users2 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const mainNavItems = [
   
];

const stateCoordinates = [
    'abia',
    'adamawa',
    'akwaibom',
    'anambra',
    'bauchi',
    'bayelsa',
    'benue',
    'borno',
    'crossriver',
    'delta',
    'ebonyi',
    'edo',
    'ekiti',
    'enugu',
    'gombe',
    'imo',
    'jigawa',
    'kaduna',
    'kano',
    'katsina',
    'kebbi',
    'kogi',
    'kwara',
    'lagos',
    'nasarawa',
    'niger',
    'ogun',
    'ondo',
    'osun',
    'oyo',
    'plateau',
    'rivers',
    'sokoto',
    'taraba',
    'yobe',
    'zamfara',
    'fct',
];
if(page.props.auth?.user?.roles.map(t=>t.name?.toLowerCase()?.replace(' ','')).includes('state_editor')) {
    mainNavItems.push(
       {
        title: 'Data Management',
        href: '/records',
        icon: LayoutGrid,
    });
}

if(page.props.auth?.user?.roles.map(t=>t.name?.toLowerCase()?.replace(' ','')).includes('admin')) {
    mainNavItems.push({
        title: 'User Management',
        href: '/users',
        icon: Users2,
        permission: 'manage_users',
    });
    mainNavItems.push({
            title: 'nigeria',
            href: '/dashboard/' + 'nigeria',
            icon: SignalMedium,
    });
}




stateCoordinates.forEach(state => {

    if(page.props.auth.states.map(t=>t.name?.toLowerCase()?.replace(' ','')).includes(state.toLowerCase())) {
        mainNavItems.push({
            title: state,
            href: '/dashboard/' + state,
            icon: SignalMedium,
        });
    }
});

const footerNavItems= [
    {
        title: 'Profile',
        href: '/profile',
        icon: User,
    },
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template> -->
