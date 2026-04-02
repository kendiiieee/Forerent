<?php

namespace App\Livewire\Navbars;

use App\Enums\Role;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SideBar extends Component
{
    public $navigations = [];
    public $sidebarOpen = true;

    #[On('profile-updated')]
    public function refreshSidebar()
    {
        // Re-render to pick up updated profile image
    }

    public function mount()
    {
        $user = Auth::user();

        if (!$user) {
            $this->navigations = [
                'dashboard' => [
                    'label' => 'Login',
                    'route' => 'login',
                    'icon'  => 'icons.dashboard',
                ]
            ];
            return;
        }

        // Logic for Logged In Users
        switch ($user->role) {
            case Role::Landlord->value:
                $this->navigations = [
                    'dashboard' => [
                        'label' => 'Dashboard',
                        'route' => 'landlord.dashboard',
                        'icon'  => 'icons.dashboard',
                    ],
                    'properties' => [
                        'label' => 'Properties',
                        'route' => 'landlord.property',
                        'icon'  => 'icons.property',
                    ],
                    'manager' => [
                        'label' => 'Managers',
                        'route' => 'landlord.manager',
                        'icon'  => 'icons.manager',
                    ],
                    'payments' => [
                        'label' => 'Payments',
                        'route' => 'landlord.payment',
                        'icon'  => 'icons.payments',
                    ],
                    'revenue' => [
                        'label' => 'Revenue',
                        'icon'  => 'icons.revenue',
                        'route' => 'landlord.revenue',
                    ],
                    'messages' => [
                        'label' => 'Messages',
                        'route' => 'landlord.messages',
                        'icon'  => 'icons.messages',
                    ],
                ];
                break;

            case 'tenant':
                $this->navigations = [
                    'dashboard' => [
                        'label' => 'Dashboard',
                        'route' => 'tenant.dashboard',
                        'icon' => 'icons.dashboard',
                    ],
                    'payments' => [
                        'label' => 'Payments',
                        'route' => 'tenant.payment',
                        'icon' => 'icons.payments',
                    ],
                    'maintenance' => [
                        'label' => 'Maintenance',
                        'route' => 'tenant.maintenance',
                        'icon' => 'icons.maintenance',
                    ],
                    'messages' => [
                        'label' => 'Messages',
                        'route' => 'tenant.messages',
                        'icon' => 'icons.messages',
                    ],
                ];
                break;

            case 'manager':
                $this->navigations = [
                    'dashboard' => [
                        'label' => 'Dashboard',
                        'route' => 'manager.dashboard',
                        'icon'  => 'icons.dashboard',
                    ],
                    'properties' => [
                        'label' => 'Properties',
                        'route' => 'manager.property',
                        'icon'  => 'icons.property',
                    ],
                    'tenants' => [
                        'label' => 'Tenants',
                        'route' => 'manager.tenant',
                        'icon'  => 'icons.tenant',
                    ],
                    'payments' => [
                        'label' => 'Payments',
                        'route' => 'manager.payment',
                        'icon'  => 'icons.payments',
                    ],
                    'maintenance' => [
                        'label' => 'Maintenance',
                        'route' => 'manager.maintenance',
                        'icon'  => 'icons.maintenance',
                    ],
                    'messages' => [
                        'label' => 'Messages',
                        'route' => 'manager.messages',
                        'icon'  => 'icons.messages',
                    ],
                ];
                break;

            default:
                $this->navigations = [
                    'dashboard' => [
                        'label' => 'Dashboard',
                        'route' => 'dashboard',
                        'icon'  => 'icons.dashboard',
                    ]
                ];
                break;
        }
    }

    public function getActiveClass($routeName)
    {
        return request()->routeIs($routeName)
            ? 'bg-[#DFE8FC] text-[#070642]'
            : 'text-[#6B7280] hover:bg-[#DFE8FC] hover:text-[#070642]';
    }

    public function render()
    {
        return view('livewire.navbars.side-bar');
    }
}



// <a href="http://localhost:8000/manager/tenant" class="flex items-center p-3 rounded-lg bg-[#DFE8FC] text-[#070642] transition-colors group" :class="!sidebarExpanded &amp;&amp; 'justify-center'" :title="!sidebarExpanded ? 'Managers' : ''" title="">
//                                     <div>
//     <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-[#070642]" width="29" height="37" viewBox="0 0 29 37" fill="none" xmlns="http://www.w3.org/2000/svg">
//         <path d="M0 6.47878L9.75082 0.537109V36.1871H0V6.47878Z" fill="currentColor"></path>
//         <path d="M18.4219 5.93863L10.2962 0.537109V36.1871H18.4219V5.93863Z" fill="currentColor"></path>
//         <path d="M28.4414 23.8973L18.9614 18.6309V36.1858H28.4414V23.8973Z" fill="currentColor"></path>
//         <path d="M1.35938 9.37367L4.20336 7.76758V10.362L1.35938 11.8446V9.37367Z" fill="#F4F7FC"></path>
//         <path d="M5.28516 7.27406L8.12915 5.66797V8.26242L5.28516 9.74496V7.27406Z" fill="#F4F7FC"></path>
//         <path d="M1.35938 16.0905L4.20336 14.4844V17.0788L1.35938 18.5614V16.0905Z" fill="#F4F7FC"></path>
//         <path d="M5.28516 13.9909L8.12915 12.3848V14.9792L5.28516 16.4618V13.9909Z" fill="#F4F7FC"></path>
//         <path d="M1.35938 22.8092L4.20336 21.2031V23.7976L1.35938 25.2801V22.8092Z" fill="#F4F7FC"></path>
//         <path d="M5.28516 20.7096L8.12915 19.1035V21.698L5.28516 23.1805V20.7096Z" fill="#F4F7FC"></path>
//         <path d="M1.35938 29.526L4.20336 27.9199V30.5144L1.35938 31.9969V29.526Z" fill="#F4F7FC"></path>
//         <path d="M5.28516 27.4264L8.12915 25.8203V28.4148L5.28516 29.8973V27.4264Z" fill="#F4F7FC"></path>
//         <path d="M27.0898 26.6588L24.2459 25.0527V27.6472L27.0898 29.1297V26.6588Z" fill="#F4F7FC"></path>
//         <path d="M23.1602 24.5592L20.3162 22.9531V25.5476L23.1602 27.0301V24.5592Z" fill="#F4F7FC"></path>
//         <path d="M27.0898 31.7916L24.2459 30.1855V32.78L27.0898 34.2625V31.7916Z" fill="#F4F7FC"></path>
//         <path d="M23.1602 29.6901L20.3162 28.084V30.6784L23.1602 32.161V29.6901Z" fill="#F4F7FC"></path>
//     </svg>
// </div>
//                                     <span x-show="sidebarExpanded" x-transition="" class="ms-3 whitespace-nowrap">
//                                         Managers
//                                     </span>
//                                 </a>
