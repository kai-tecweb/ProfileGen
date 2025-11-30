import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';

interface ProposalsEditProps {
    proposal: {
        id: number;
        x_profile: string;
        instagram_profile: string;
        coconala_profile: string;
        product_design: string;
    };
}

export default function Edit({ proposal }: ProposalsEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        x_profile: proposal.x_profile,
        instagram_profile: proposal.instagram_profile,
        coconala_profile: proposal.coconala_profile,
        product_design: proposal.product_design,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('proposals.update', proposal.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    提案編集
                </h2>
            }
        >
            <Head title="提案編集" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={submit}>
                                <div className="mb-4">
                                    <InputLabel htmlFor="x_profile" value="X用プロフィール *" />
                                    <textarea
                                        id="x_profile"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={5}
                                        value={data.x_profile}
                                        onChange={(e) => setData('x_profile', e.target.value)}
                                        required
                                    />
                                    <p className="text-xs text-gray-500 mt-1">
                                        文字数: {data.x_profile.length}文字（160文字以内推奨）
                                    </p>
                                    <InputError message={errors.x_profile} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="instagram_profile" value="Instagram用プロフィール *" />
                                    <textarea
                                        id="instagram_profile"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={5}
                                        value={data.instagram_profile}
                                        onChange={(e) => setData('instagram_profile', e.target.value)}
                                        required
                                    />
                                    <p className="text-xs text-gray-500 mt-1">
                                        文字数: {data.instagram_profile.length}文字（150文字以内推奨）
                                    </p>
                                    <InputError message={errors.instagram_profile} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="coconala_profile" value="ココナラ用プロフィール *" />
                                    <textarea
                                        id="coconala_profile"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={10}
                                        value={data.coconala_profile}
                                        onChange={(e) => setData('coconala_profile', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.coconala_profile} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="product_design" value="商品設計案 *" />
                                    <textarea
                                        id="product_design"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={15}
                                        value={data.product_design}
                                        onChange={(e) => setData('product_design', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.product_design} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-end space-x-4">
                                    <Link href={route('proposals.show', proposal.id)}>
                                        <Button type="button" variant="secondary">
                                            キャンセル
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? '更新中...' : '更新'}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

