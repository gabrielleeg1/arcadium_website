import { AxiosInstance } from 'axios'
import { Post, User } from '~/services/entities'
import { Paginator } from './paginator'

type Any = {
  [key: string]: Any
}

export class PostService {
  public constructor(private readonly api: AxiosInstance) {}

  public async findAll(page = 1): Promise<Paginator<Post>> {
    const response = await this.api.get<Paginator<any>>('posts', {
      params: {
        page,
      },
    })

    response.data.data = response.data.data.map(
      post =>
        new Post(
          post.id,
          post.title,
          post.description,
          new User(
            post.created_by.id,
            post.created_by.name,
            post.created_by.user_name,
            post.created_by.avatar,
            post.created_by.email
          ),
          new Date(post.created_at),
          new Date(post.updated_at)
        )
    )

    return response.data
  }

  public async hasLiked(postId: number): Promise<boolean> {
    try {
      const hasLiked = await this.api.get(`posts/${postId}/liked`)

      return hasLiked.data
    } catch {
      // Ignore if has error
    }

    return false
  }

  public async like(postId: number): Promise<void> {
    try {
      await this.api.get(`posts/${postId}/like`)
    } catch {
      // Ignore if has error
    }
  }

  public async unlike(postId: number): Promise<void> {
    try {
      await this.api.get(`posts/${postId}/like`)
    } catch {
      // Ignore if has error
    }
  }
}
