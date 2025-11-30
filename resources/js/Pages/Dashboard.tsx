import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { route } from 'ziggy-js';

export default function Dashboard({ stats, recentProposals }: PageProps) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    ダッシュボード
                </h2>
            }
        >
            <Head title="ダッシュボード" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* 統計情報 */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="text-sm font-medium text-gray-500">記事数</div>
                                <div className="text-3xl font-bold text-gray-900">
                                    {stats?.articles_count ?? 0}
                                </div>
                                <Link
                                    href={route('articles.index')}
                                    className="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block"
                                >
                                    記事管理へ →
                                </Link>
                            </div>
                        </div>
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="text-sm font-medium text-gray-500">クライアント数</div>
                                <div className="text-3xl font-bold text-gray-900">
                                    {stats?.clients_count ?? 0}
                                </div>
                                <Link
                                    href={route('clients.index')}
                                    className="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block"
                                >
                                    クライアント管理へ →
                                </Link>
                            </div>
                        </div>
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="text-sm font-medium text-gray-500">提案数</div>
                                <div className="text-3xl font-bold text-gray-900">
                                    {stats?.proposals_count ?? 0}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* 最近の提案 */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">
                                最近の提案
                            </h3>
                            {recentProposals && recentProposals.length > 0 ? (
                                <div className="space-y-4">
                                    {recentProposals.map((proposal) => (
                                        <div
                                            key={proposal.id}
                                            className="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0"
                                        >
                                            <div className="flex justify-between items-start">
                                                <div>
                                                    <Link
                                                        href={route('proposals.show', proposal.id)}
                                                        className="text-blue-600 hover:text-blue-800 font-medium"
                                                    >
                                                        {proposal.client?.name}様の提案
                                                    </Link>
                                                    <div className="text-sm text-gray-500 mt-1">
                                                        {new Date(proposal.created_at).toLocaleString('ja-JP')}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500">提案はまだありません</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
