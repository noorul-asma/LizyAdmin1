<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the real LizyNet service catalogue (the same 11 services that used
 * to live only as hardcoded content in the LizyNet site's own js/site.js)
 * into the services table, so switching LizyNet's CONFIG.adminApiBase on
 * shows the exact same content it already had - nothing regresses, it's
 * just now editable from Lizy Admin instead of baked into the frontend.
 *
 * Slugs match the LizyNet frontend's existing static service-<slug>.html
 * files and js/data.js SERVICES array exactly, so both the dynamic
 * service.html?slug=X route and any bookmarked static page resolve to
 * this same record.
 */
class LizyNetServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::where('module', 'service')->pluck('id', 'slug');

        foreach ($this->services() as $data) {
            $categoryId = $categories[$data['category_slug']] ?? null;
            if (! $categoryId) {
                continue; // category missing (seedCategories() didn't run) - skip rather than fail
            }

            Service::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $categoryId,
                    'name' => $data['name'],
                    'provider_name' => 'Lizyweb',
                    'pricing_type' => 'quote',
                    'description' => $data['desc'],
                    'intro' => $data['intro'],
                    'features' => $data['includes'],
                    'outcomes' => $data['outcomes'],
                    'process' => $data['process'],
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }
    }

    private function services(): array
    {
        return [
            [
                'slug' => 'static-website-development', 'name' => 'Static Website Development', 'category_slug' => Str::slug('Web Development'),
                'desc' => 'Fast-loading, secure websites for businesses that need a strong online presence.',
                'intro' => 'A focused, high-performance website for businesses that need a strong digital first impression without unnecessary complexity.',
                'includes' => ['Responsive page design', 'Fast-loading front-end build', 'Contact and enquiry flow', 'SEO-ready page structure', 'Secure deployment support'],
                'outcomes' => ['Clear online presence', 'Faster visitor experience', 'Mobile-ready design'],
                'process' => ['Discover your goals', 'Shape the page structure', 'Design and build', 'Test, refine and launch'],
            ],
            [
                'slug' => 'dynamic-website-development', 'name' => 'Dynamic Website Development', 'category_slug' => Str::slug('Web Development'),
                'desc' => 'Interactive, database-driven websites with real-time content updates.',
                'intro' => 'A flexible, content-driven website built around changing information, user interactions and business workflows.',
                'includes' => ['Database and admin integration', 'Dynamic content sections', 'Secure forms and workflows', 'User-friendly content controls', 'Performance testing'],
                'outcomes' => ['Easier content updates', 'Scalable website structure', 'More interactive journeys'],
                'process' => ['Map the workflow', 'Plan data and features', 'Build the experience', 'Test and go live'],
            ],
            [
                'slug' => 'ecommerce-website-development', 'name' => 'Ecommerce Website Development', 'category_slug' => Str::slug('Web Development'),
                'desc' => 'Online stores designed to sell products seamlessly, from catalogue to checkout.',
                'intro' => 'A conversion-focused online store designed to make product discovery, ordering and customer management feel simple.',
                'includes' => ['Product catalogue setup', 'Cart and checkout journey', 'Order management flow', 'Mobile commerce experience', 'Store performance testing'],
                'outcomes' => ['Simpler online selling', 'Smoother checkout flow', 'Ready-to-grow store'],
                'process' => ['Plan your store', 'Design the buying journey', 'Build core commerce', 'Test before launch'],
            ],
            [
                'slug' => 'seo', 'name' => 'Search Engine Optimization (SEO)', 'category_slug' => Str::slug('Digital Marketing'),
                'desc' => 'Keyword research, on-page and technical SEO to improve search visibility.',
                'intro' => 'A practical search visibility plan that improves how your website is structured, understood and discovered over time.',
                'includes' => ['Keyword opportunity research', 'On-page optimisation', 'Technical SEO review', 'Content recommendations', 'Performance reporting'],
                'outcomes' => ['Stronger search foundation', 'Better content targeting', 'Clearer visibility tracking'],
                'process' => ['Audit the website', 'Prioritise opportunities', 'Optimise key pages', 'Track and improve'],
            ],
            [
                'slug' => 'social-media-marketing', 'name' => 'Social Media Marketing', 'category_slug' => Str::slug('Digital Marketing'),
                'desc' => 'Content, community and campaigns that grow engagement and following.',
                'intro' => 'A consistent social presence shaped around your audience, brand voice and business goals.',
                'includes' => ['Content direction', 'Monthly content planning', 'Creative campaign support', 'Community engagement', 'Performance insights'],
                'outcomes' => ['More consistent presence', 'Stronger audience engagement', 'Clearer campaign direction'],
                'process' => ['Understand the audience', 'Plan the content', 'Create and publish', 'Review and improve'],
            ],
            [
                'slug' => 'ppc-advertising', 'name' => 'Pay-Per-Click (PPC) Advertising', 'category_slug' => Str::slug('Digital Marketing'),
                'desc' => 'Targeted Google & Meta ad campaigns that drive traffic and leads instantly.',
                'intro' => 'Targeted paid campaigns built to put the right offer in front of the right audience and learn quickly from performance.',
                'includes' => ['Campaign strategy', 'Audience targeting', 'Ad creative direction', 'Conversion tracking', 'Ongoing optimisation'],
                'outcomes' => ['Faster campaign testing', 'More measurable traffic', 'Clearer lead insights'],
                'process' => ['Define the objective', 'Build campaign assets', 'Launch and monitor', 'Optimise performance'],
            ],
            [
                'slug' => 'mobile-app-development', 'name' => 'Mobile Application Development', 'category_slug' => Str::slug('Engineering'),
                'desc' => 'Custom iOS and Android apps built for performance and scalability.',
                'intro' => 'Custom mobile applications planned around real user journeys, reliable performance and a scalable technical foundation.',
                'includes' => ['iOS and Android planning', 'UI and user flow design', 'API and backend integration', 'Testing and QA support', 'Launch preparation'],
                'outcomes' => ['Clear mobile experience', 'Scalable architecture', 'Reliable release process'],
                'process' => ['Define the product', 'Prototype the journey', 'Build core features', 'Test and release'],
            ],
            [
                'slug' => 'ai-agent-development', 'name' => 'AI Agent Development', 'category_slug' => Str::slug('Engineering'),
                'desc' => 'Intelligent agents that automate tasks and enhance customer support.',
                'intro' => 'Purpose-built AI agents that help automate repetitive work and make customer or internal workflows more responsive.',
                'includes' => ['Use-case discovery', 'Conversation flow design', 'Knowledge and tool integration', 'Guardrails and testing', 'Monitoring guidance'],
                'outcomes' => ['Less repetitive work', 'Faster responses', 'More scalable support'],
                'process' => ['Choose the workflow', 'Design the agent', 'Connect knowledge and tools', 'Test, refine and deploy'],
            ],
            [
                'slug' => 'software-development', 'name' => 'Software Development', 'category_slug' => Str::slug('Engineering'),
                'desc' => 'Custom software solutions designed to improve operations and support growth.',
                'intro' => 'Custom software designed around the way your business actually operates, from everyday workflows to larger digital systems.',
                'includes' => ['Requirements discovery', 'Workflow and architecture planning', 'Custom feature development', 'Integration support', 'Testing and rollout'],
                'outcomes' => ['Better operational fit', 'More connected workflows', 'A scalable digital foundation'],
                'process' => ['Understand operations', 'Plan the solution', 'Develop in stages', 'Validate and roll out'],
            ],
            [
                'slug' => 'branding', 'name' => 'Branding', 'category_slug' => Str::slug('Design'),
                'desc' => 'Strong, consistent brand identities that leave a lasting impression.',
                'intro' => 'A cohesive visual and verbal identity that helps your business look consistent, recognisable and ready to communicate clearly.',
                'includes' => ['Brand discovery', 'Visual identity direction', 'Logo and design system', 'Core brand guidelines', 'Launch-ready assets'],
                'outcomes' => ['Clearer brand presence', 'Consistent communication', 'Stronger recognition'],
                'process' => ['Discover the brand', 'Define the direction', 'Create the identity', 'Package for rollout'],
            ],
            [
                'slug' => 'saas-app-development', 'name' => 'SaaS App Development', 'category_slug' => Str::slug('Engineering'),
                'desc' => 'Secure, scalable SaaS applications built to grow with your business.',
                'intro' => 'Secure, scalable SaaS products built around product logic, user journeys and the operational needs of a growing platform.',
                'includes' => ['Product architecture', 'User and role management', 'Dashboard and workflow design', 'API and cloud integration', 'Scalability planning'],
                'outcomes' => ['Product-ready foundation', 'Flexible user workflows', 'Built to support growth'],
                'process' => ['Shape the product', 'Map the architecture', 'Build the platform', 'Test and scale'],
            ],
        ];
    }
}
